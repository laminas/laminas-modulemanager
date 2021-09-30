<?php

namespace LaminasTest\ModuleManager\Listener\TestAsset;

class ServiceProviderModule
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getServiceConfig()
    {
        return $this->config;
    }
}
