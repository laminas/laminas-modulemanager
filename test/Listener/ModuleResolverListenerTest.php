<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\Loader\AutoloaderFactory;
use Laminas\Loader\ModuleAutoloader;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use PHPUnit_Framework_TestCase as TestCase;

class ModuleResolverListenerTest extends TestCase
{

    public function setUp()
    {
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Store original include_path
        $this->includePath = get_include_path();

        $autoloader = new ModuleAutoloader(array(
            dirname(__DIR__) . '/TestAsset',
        ));
        $autoloader->register();
    }

    public function tearDown()
    {
        // Restore original autoloaders
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

        // Restore original include_path
        set_include_path($this->includePath);
    }

    public function testModuleResolverListenerCanResolveModuleClasses()
    {
        $moduleResolver = new ModuleResolverListener;
        $e = new ModuleEvent;

        $e->setModuleName('ListenerTestModule');
        $this->assertInstanceOf('ListenerTestModule\Module', $moduleResolver($e));

        $e->setModuleName('DoesNotExist');
        $this->assertFalse($moduleResolver($e));
    }
}
