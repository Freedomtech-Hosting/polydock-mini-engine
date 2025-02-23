<?php

namespace App;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\PolydockAppLoggerInterface;
use FreedomtechHosting\PolydockApp\PolydockServiceProviderInterface;
use FreedomtechHosting\PolydockApp\PolydockEngineInterface;

class Engine implements PolydockEngineInterface
{
    /**
     * @var PolydockAppInstanceInterface
     */
    private PolydockAppInstanceInterface $appInstance;

    /**
     * @var PolydockAppLoggerInterface
     */
    protected PolydockAppLoggerInterface $logger;

    /**
     * @var array<string, PolydockServiceProviderInterface>
     */
    protected array $polydockServiceProviderSingletonInstances = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $polydockServiceProviderSingletonConfig = [];

    /**
     * Constructor
     * @param PolydockAppLoggerInterface $logger The logger to set
     */
    public function __construct(PolydockAppLoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->polydockServiceProviderSingletonInstances = [];
        $this->polydockServiceProviderSingletonConfig = config('polydock.service_providers_singletons');
        $this->initializePolydockServiceProviders($this->polydockServiceProviderSingletonConfig);
    }

    /**
     * Set the logger for the engine
     * @param PolydockAppLoggerInterface $logger The logger to set
     * @return self Returns the instance for method chaining
     */
    public function setLogger(PolydockAppLoggerInterface $logger) : self
    {
        $this->logger = $logger;
        return $this;
    }   

    /**
     * Get the logger for the engine
     * @return PolydockAppLoggerInterface The logger
     */
    public function getLogger() : PolydockAppLoggerInterface
    {
        return $this->logger;
    }

    /**
     * Initialize the polydock service providers
     * @param array<string, array<string, mixed>> $config The config for the polydock service providers
     * @return self Returns the instance for method chaining
     */ 
    public function initializePolydockServiceProviders(array $config) : self
    {
        $this->info('Initializing polydock service providers');
        foreach($config as $polydockServiceProviderKey => $polydockServiceProviderConfig) {
            $polydockServiceProviderClass = $polydockServiceProviderConfig['class'];
            
            $this->info('Initializing polydock service provider ', ['polydockServiceProviderClass' => $polydockServiceProviderClass]);
            
            if(! class_exists($polydockServiceProviderClass)) {
                throw new PolydockMiniEngineServiceProviderNotFoundException($polydockServiceProviderClass);
            }
    
            $provider = new $polydockServiceProviderClass($polydockServiceProviderConfig, $this->getLogger()); 
    
            if(! $provider instanceof PolydockServiceProviderInterface) { 
                throw new PolydockMiniEngineServiceProviderNotFoundException($polydockServiceProviderClass);   
            }

            $this->polydockServiceProviderSingletonInstances[$polydockServiceProviderKey] = $provider;
        }
        return $this;
    }

    /**
     * Get a polydock service provider singleton instance
     * @param string $polydockServiceProviderClass The class name of the polydock service provider
     * @return PolydockServiceProviderInterface The polydock service provider instance
     */
    public function getPolydockServiceProviderSingletonInstance(string $polydockServiceProviderClass) : PolydockServiceProviderInterface
    {    
        if(! isset($this->polydockServiceProviderSingletonInstances[$polydockServiceProviderClass])) {
            throw new PolydockMiniEngineServiceProviderNotFoundException($polydockServiceProviderClass);
        }

        return $this->polydockServiceProviderSingletonInstances[$polydockServiceProviderClass];
    }

    /**
     * Run a test for the app instance
     * @param string $polydockAppClass The class name of the app instance
     * @return void
     */
    public function runFullTest(string $polydockAppClass, 
        string $appName, 
        string $appDescription, 
        string $appAuthor, 
        string $appWebsite, 
        string $appSupportEmail, 
        array $appConfiguration)
    {

        if(!class_exists($polydockAppClass)) {
            throw new PolydockMiniEngineAppNotFoundException('Class ' . $polydockAppClass . ' not found');
        }

        $app = new $polydockAppClass(
            $appName, 
            $appDescription, 
            $appAuthor, 
            $appWebsite, 
            $appSupportEmail, 
            $appConfiguration   
        );

        $this->logger->info('--------------------------------');
        $this->info("App Name: " . $appName);
        $this->info("App Description: " . $appDescription);
        $this->info("App Author: " . $appAuthor);
        $this->info("App Website: " . $appWebsite);
        $this->info("App Support Email: " . $appSupportEmail);
        $this->info("App Configuration: " . json_encode($appConfiguration));
        $this->logger->info('--------------------------------');
        
        $app->setLogger($this->logger);

        $this->appInstance = new PolydockAppInstance();
        $this->appInstance->setLogger($this->logger);
        $this->appInstance->setEngine($this);

        $this->appInstance->setAppType($polydockAppClass);
        $this->appInstance->setApp($app);


        if(is_array($appConfiguration)) {
            foreach($appConfiguration as $key => $value) {
                $this->appInstance->storeKeyValue($key, $value);
            }
        }

        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_PRE_CREATE, "Initializing the test");
        $this->info('Running test', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        
        $this->logger->info('--------------------------------');
        $this->runPreCreate();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::PRE_CREATE_COMPLETED) {
            $this->error('Pre-create failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Pre-create completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }
        $this->logger->info('--------------------------------');
        $this->runCreate();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::CREATE_COMPLETED) {
            $this->error('Create failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Create completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }

        $this->logger->info('--------------------------------');
        $this->runPostCreate();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::POST_CREATE_COMPLETED) {
            $this->error('Post-create failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Post-create completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }

        $this->logger->info('--------------------------------');
        $this->runPreDeploy();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::PRE_DEPLOY_COMPLETED) {
            $this->error('Pre-deploy failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Pre-deploy completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }

        $this->logger->info('--------------------------------');
        $this->runDeploy();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::DEPLOY_COMPLETED) {
            $this->error('Deploy failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Deploy completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }

        $this->logger->info('--------------------------------');        
        $this->runPostDeploy();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::POST_DEPLOY_COMPLETED) {
            $this->error('Post-deploy failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Post-deploy completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }
        
        $this->logger->info('--------------------------------');
        $this->runPreRemove();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::PRE_REMOVE_COMPLETED) {
            $this->error('Pre-remove failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Pre-remove completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }
        
        $this->logger->info('--------------------------------');
        $this->runRemove();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::REMOVE_COMPLETED) {
            $this->error('Remove failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Remove completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }

        $this->logger->info('--------------------------------');
        $this->runPostRemove();

        if($this->appInstance->getStatus() != PolydockAppInstanceStatus::POST_REMOVE_COMPLETED) {
            $this->error('Post-remove failed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
            return;
        } else {
            $this->info('Post-remove completed', ['app' => $polydockAppClass, 'status' => $this->appInstance->getStatus(), 'statusMessage' => $this->appInstance->getStatusMessage()]);
        }
    }

    /**
     * Log an info message
     * @param string $message The message to log
     * @param array<string, mixed> $context The context for the message
     * @return self Returns the instance for method chaining
     */
    public function info(string $message, array $context = []) : self
    {
        $this->logger->info($message, $context);
        return $this;
    }

    /**
     * Log an error message
     * @param string $message The message to log
     * @param array<string, mixed> $context The context for the message
     * @return self Returns the instance for method chaining
     */
    public function error(string $message, array $context = []) : self
    {
        $this->logger->error($message, $context);
        return $this;
    }

    /**
     * Log a warning message
     * @param string $message The message to log
     * @param array<string, mixed> $context The context for the message
     * @return self Returns the instance for method chaining
     */ 
    public function warning(string $message, array $context = []) : self
    {
        $this->logger->warning($message, $context);
        return $this;
    }

    /**
     * Log a debug message
     * @param string $message The message to log
     * @param array<string, mixed> $context The context for the message
     * @return self Returns the instance for method chaining
     */  
    public function debug(string $message, array $context = []) : self
    {
        $this->logger->debug($message, $context);
        return $this;
    }   

    /**
     * Run the pre-create step
     * @return void
     */
    public function runPreCreate()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_PRE_CREATE);
        $this->info('Calling pre-create', ['engine' => self::class, 'location' => 'runPreCreate']);
        $this->appInstance->getApp()->preCreateAppInstance($this->appInstance);
    }

    /** 
     * Run the create step
     * @return void
     */
    public function runCreate()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_CREATE);
        $this->info('Calling create', ['engine' => self::class, 'location' => 'runCreate']);
        $this->appInstance->getApp()->createAppInstance($this->appInstance);
    }

    /**
     * Run the post-create step
     * @return void
     */
    public function runPostCreate()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_POST_CREATE);
        $this->info('Calling post-create', ['engine' => self::class, 'location' => 'runPostCreate']);
        $this->appInstance->getApp()->postCreateAppInstance($this->appInstance);
    }

    /**
     * Run the pre-deploy step
     * @return void
     */
    public function runPreDeploy()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_PRE_DEPLOY);
        $this->info('Calling pre-deploy', ['engine' => self::class, 'location' => 'runPreDeploy']);
        $this->appInstance->getApp()->preDeployAppInstance($this->appInstance);
    }

    /**
     * Run the deploy step
     * @return void
     */
    public function runDeploy()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_DEPLOY);
        $this->info('Calling deploy', ['engine' => self::class, 'location' => 'runDeploy']);
        $this->appInstance->getApp()->deployAppInstance($this->appInstance);
    }
    
    /**
     * Run the post-deploy step
     * @return void
     */
    public function runPostDeploy()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_POST_DEPLOY);
        $this->info('Calling post-deploy', ['engine' => self::class, 'location' => 'runPostDeploy']);
        $this->appInstance->getApp()->postDeployAppInstance($this->appInstance);
    }

    /**
     * Run the pre-remove step
     * @return void
     */
    public function runPreRemove()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_PRE_REMOVE);
        $this->info('Calling pre-remove', ['engine' => self::class, 'location' => 'runPreRemove']);
        $this->appInstance->getApp()->preRemoveAppInstance($this->appInstance);
    }

    /**
     * Run the remove step
     * @return void
     */
    public function runRemove()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_REMOVE);
        $this->info('Calling remove', ['engine' => self::class, 'location' => 'runRemove']);
        $this->appInstance->getApp()->removeAppInstance($this->appInstance);
    }

    /**
     * Run the post-remove step
     * @return void
     */
    public function runPostRemove()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_POST_REMOVE);
        $this->info('Calling post-remove', ['engine' => self::class, 'location' => 'runPostRemove']);
        $this->appInstance->getApp()->postRemoveAppInstance($this->appInstance);
    }
    
}
