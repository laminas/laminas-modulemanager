<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

class CustomPluginManager extends AbstractPluginManager
{
    /** @var string */
    protected $instanceOf = CustomPluginInterface::class;

    /** @param mixed $plugin */
    public function validate($plugin): void
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException();
        }
    }

    public function validatePlugin(mixed $plugin): void
    {
        $this->validate($plugin);
    }
}
