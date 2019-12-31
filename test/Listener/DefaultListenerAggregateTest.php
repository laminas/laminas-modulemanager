<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\Loader\AutoloaderFactory;
use Laminas\ModuleManager\Listener\DefaultListenerAggregate;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit_Framework_TestCase as TestCase;

class DefaultListenerAggregateTest extends TestCase
{
    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Store original include_path
        $this->includePath = get_include_path();

        $this->defaultListeners = new DefaultListenerAggregate(
            new ListenerOptions(array(
                'module_paths'         => array(
                    realpath(__DIR__ . '/TestAsset'),
                ),
            ))
        );
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

    public function testDefaultListenerAggregateCanAttachItself()
    {
        $moduleManager = new ModuleManager(array('ListenerTestModule'));
        $moduleManager->getEventManager()->attachAggregate(new DefaultListenerAggregate);

        $events = $moduleManager->getEventManager()->getEvents();
        $expectedEvents = array(
            'loadModules' => array(
                'Laminas\Loader\ModuleAutoloader',
                'config-pre' => 'Laminas\ModuleManager\Listener\ConfigListener',
                'config-post' => 'Laminas\ModuleManager\Listener\ConfigListener',
                'Laminas\ModuleManager\Listener\LocatorRegistrationListener',
                'Laminas\ModuleManager\ModuleManager',
            ),
            'loadModule.resolve' => array(
                'Laminas\ModuleManager\Listener\ModuleResolverListener',
            ),
            'loadModule' => array(
                'Laminas\ModuleManager\Listener\AutoloaderListener',
                'Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener',
                'Laminas\ModuleManager\Listener\InitTrigger',
                'Laminas\ModuleManager\Listener\OnBootstrapListener',
                'Laminas\ModuleManager\Listener\ConfigListener',
                'Laminas\ModuleManager\Listener\LocatorRegistrationListener',
            ),
        );
        foreach ($expectedEvents as $event => $expectedListeners) {
            $this->assertContains($event, $events);
            $listeners = $moduleManager->getEventManager()->getListeners($event);
            $this->assertSame(count($expectedListeners), count($listeners));
            foreach ($listeners as $listener) {
                $callback = $listener->getCallback();
                if (is_array($callback)) {
                    $callback = $callback[0];
                }
                $listenerClass = get_class($callback);
                $this->assertContains($listenerClass, $expectedListeners);
            }
        }
    }

    public function testDefaultListenerAggregateCanDetachItself()
    {
        $listenerAggregate = new DefaultListenerAggregate;
        $moduleManager     = new ModuleManager(array('ListenerTestModule'));

        $this->assertEquals(1, count($moduleManager->getEventManager()->getEvents()));

        $listenerAggregate->attach($moduleManager->getEventManager());
        $this->assertEquals(4, count($moduleManager->getEventManager()->getEvents()));

        $listenerAggregate->detach($moduleManager->getEventManager());
        $this->assertEquals(1, count($moduleManager->getEventManager()->getEvents()));
    }
}
