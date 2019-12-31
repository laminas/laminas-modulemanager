<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\Loader\AutoloaderFactory;
use Laminas\Loader\ModuleAutoloader;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\Listener\OnBootstrapListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use LaminasTest\ModuleManager\TestAsset\MockApplication;
use PHPUnit_Framework_TestCase as TestCase;

class OnBootstrapListenerTest extends TestCase
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

        $sharedEvents = new SharedEventManager();
        $this->moduleManager = new ModuleManager(array());
        $this->moduleManager->getEventManager()->setSharedManager($sharedEvents);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener, 1000);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, new OnBootstrapListener, 1000);

        $this->application = new MockApplication;
        $events            = new EventManager(array('Laminas\Mvc\Application', 'LaminasTest\Module\TestAsset\MockApplication', 'application'));
        $events->setSharedManager($sharedEvents);
        $this->application->setEventManager($events);
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

    public function testOnBootstrapMethodCalledByOnBootstrapListener()
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(array('ListenerTestModule'));
        $moduleManager->loadModules();
        $this->application->bootstrap();
        $modules = $moduleManager->getLoadedModules();
        $this->assertTrue($modules['ListenerTestModule']->onBootstrapCalled);
    }
}
