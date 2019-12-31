<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager;

use Laminas\Loader\AutoloaderFactory;

/**
 * Offer common setUp/tearDown methods for preserve current autoload functions and include paths.
 */
trait ResetAutoloadFunctionsTrait
{
    /**
     * @var callable[]
     */
    private $loaders;

    /**
     * @var string
     */
    private $includePath;

    /**
     * @before
     */
    protected function preserveAutoloadFunctions()
    {
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = [];
        }
    }

    /**
     * @before
     */
    protected function preserveIncludePath()
    {
        $this->includePath = get_include_path();
    }

    /**
     * @after
     */
    protected function restoreAutoloadFunctions()
    {
        AutoloaderFactory::unregisterAutoloaders();
        $loaders = spl_autoload_functions();
        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                spl_autoload_unregister($loader);
            }
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }
    }

    /**
     * @before
     */
    protected function restoreIncludePath()
    {
        set_include_path($this->includePath);
    }
}
