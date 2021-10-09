<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

class ServiceProviderModule
{
    /** @var mixed */
    public $config;

    /** @param mixed $config */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /** @return mixed */
    public function getServiceConfig()
    {
        return $this->config;
    }
}
