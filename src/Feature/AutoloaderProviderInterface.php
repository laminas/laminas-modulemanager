<?php

namespace Laminas\ModuleManager\Feature;

/**
 * Autoloader provider interface
 */
interface AutoloaderProviderInterface
{
    /**
     * Return an array for passing to Laminas\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig();
}
