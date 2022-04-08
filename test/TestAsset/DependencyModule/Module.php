<?php

declare(strict_types=1);

namespace DependencyModule;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;
use SomeModule\Module as SomeModule;

class Module implements ConfigProviderInterface, DependencyIndicatorInterface
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getModuleDependencies()
    {
        return [
            SomeModule::class,
        ];
    }
}
