<?php

namespace App;

class PolydockMiniEngineAppNotFoundException extends \Exception
{
    public function __construct(string $appClass)
    {
        parent::__construct('App class ' . $appClass . ' not found');
    }
}