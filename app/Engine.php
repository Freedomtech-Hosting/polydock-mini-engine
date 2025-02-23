<?php

namespace App;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;

// use FreedomtechHosting\PolydockAppAmazeeioGeneric\PolydockApp;

class Engine
{
    private PolydockAppInstanceInterface $appInstance;

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

        $this->appInstance = new PolydockAppInstance();
        $this->appInstance->setAppType($polydockAppClass);
        $this->appInstance->setApp($app);

        $this->appInstance->setStatus(PolydockAppInstanceStatus::PENDING_PRE_CREATE);
        $this->log('Running test with ' . $polydockAppClass);
    }

    public function log(string $message)
    {
        $date = date('Y-m-d H:i:s');
        echo $date . ' - ' . $message . PHP_EOL;
    }

    public function runPreCreate()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PRE_CREATE_RUNNING);
    }

    public function runPostCreate()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::POST_CREATE_RUNNING);
    }

    public function runPreDeploy()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PRE_DEPLOY_RUNNING);
    }

    public function runDeploy()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::DEPLOY_RUNNING);
    }
    

    public function runPostDeploy()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::POST_DEPLOY_RUNNING);
    }

    public function runPreRemove()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::PRE_REMOVE_RUNNING);
    }
    
    public function runRemove()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::REMOVED);
    }

    public function runPostRemove()
    {
        $this->appInstance->setStatus(PolydockAppInstanceStatus::POST_REMOVE_RUNNING);
    }
    
}
