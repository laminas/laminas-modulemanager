<?php

namespace LaminasTest\ModuleManager\Listener\TestAsset;

class CustomPluginProviderModule implements CustomPluginProviderInterface
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
