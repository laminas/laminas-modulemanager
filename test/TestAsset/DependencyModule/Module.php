<?php

declare(strict_types=1);

namespace DependencyModule;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements ConfigProviderInterface, DependencyIndicatorInterface
{
    public function getConfig()
    {
        return [];
    }

    public function getModuleDependencies()
    {
        return [
            \SomeModule\Module::class,
        ];
    }
}
