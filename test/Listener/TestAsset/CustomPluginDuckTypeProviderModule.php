<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

class CustomPluginDuckTypeProviderModule
{
    public function __construct(public mixed $config)
    {
    }

    /** @return mixed */
    public function getCustomPluginConfig()
    {
        return $this->config;
    }
}
