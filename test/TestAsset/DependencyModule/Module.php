<?php

declare(strict_types=1);

namespace DependencyModule;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

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
            \SomeModule\Module::class,
        ];
    }
}
