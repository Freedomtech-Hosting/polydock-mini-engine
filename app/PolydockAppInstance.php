<?php

namespace App;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\PolydockAppInterface;

class PolydockAppInstance implements PolydockAppInstanceInterface
{
    /**
     * The status of the app instance
     * @var PolydockAppInstanceStatus
     */
    private PolydockAppInstanceStatus $status;

    /**
     * The key-value store for the app instance
     * @var array
     */
    private array $keyValue = [];

    /**
     * The type of the app instance
     * @var string
     */
    private string $appType;

    /**
     * The app for the app instance
     * @var PolydockAppInterface
     */
    private PolydockAppInterface $app;

    /**
     * Set the app for the app instance
     * @param PolydockAppInterface $app The app to set
     * @return self Returns the instance for method chaining
     */
    public function setApp(PolydockAppInterface $app) : self
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the app for the app instance
     * @return PolydockAppInterface The app
     */
    public function getApp() : PolydockAppInterface
    {
        return $this->app;
    }

    /**
     * Set the type of the app instance
     * @param string $appType The type of the app instance
     * @return self Returns the instance for method chaining
     */
    public function setAppType(string $appType) : self
    {

        if(! class_exists($appType)) {
            throw new PolydockMiniEngineAppNotFoundException($appType);
        }
        
        $this->appType = $appType;

        return $this;
    }

    /**
     * Get the type of the app instance
     * @return string The type of the app instance
     */
    public function getAppType() : string
    {
        return $this->appType;
    }

    /**
     * Set the status of the app instance
     * @param PolydockAppInstanceStatus $status The new status to set
     * @return self Returns the instance for method chaining
     */
    public function setStatus(PolydockAppInstanceStatus $status) : self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get the status of the app instance
     * @return PolydockAppInstanceStatus The current status enum value
     */
    public function getStatus() : PolydockAppInstanceStatus
    {
        return $this->status;
    }

    /**
     * Store a key-value pair for the app instance
     * @param string $key The key to store
     * @param string $value The value to store
     * @return self Returns the instance for method chaining
     */
    public function storeKeyValue(string $key, string $value) : self
    {
        $this->keyValue[$key] = $value;
        return $this;
    }   

    /**
     * Get a stored value by key
     * @param string $key The key to retrieve
     * @return string The stored value, or empty string if not found
     */
    public function getKeyValue(string $key) : string
    {
        return $this->keyValue[$key];
    }

    /**
     * Delete a stored key-value pair
     * @param string $key The key to delete
     * @return self Returns the instance for method chaining
     */
    public function deleteKeyValue(string $key) : self
    {
        unset($this->keyValue[$key]);
        return $this;
    }

    /**
     * Log a message for the app instance
     * @param string $message The message to log
     * @return self Returns the instance for method chaining
     */
    public function log(string $message) : self
    {
        echo $message . PHP_EOL;
        return $this;
    }
}