<?php

namespace LaminasTest\ModuleManager\Listener\TestAsset;

class CustomPluginDuckTypeProviderModule
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getCustomPluginConfig()
    {
        return $this->config;
    }
}
