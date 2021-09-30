<?php

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

class CustomPluginManager extends AbstractPluginManager
{
    protected $instanceOf = CustomPluginInterface::class;

    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException();
        }
    }

    public function validatePlugin($plugin)
    {
        $this->validate($plugin);
    }
}
