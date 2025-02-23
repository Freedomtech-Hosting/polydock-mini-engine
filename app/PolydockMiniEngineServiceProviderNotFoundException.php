<?php

namespace App;

class PolydockMiniEngineServiceProviderNotFoundException extends \Exception
{
    public function __construct(string $polydockServiceProviderClass)
    {
        parent::__construct('Service provider class ' . $polydockServiceProviderClass . ' not found');
    }
}