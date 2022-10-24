<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

class ServiceProviderModule
{
    public function __construct(public mixed $config)
    {
    }

    /** @return mixed */
    public function getServiceConfig()
    {
        return $this->config;
    }
}
