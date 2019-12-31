<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\ModuleManager\Listener\DefaultListenerAggregate;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\ModuleManager;

/**
 * @covers Laminas\ModuleManager\Listener\AbstractListener
 * @covers Laminas\ModuleManager\Listener\DefaultListenerAggregate
 */
class DefaultListenerAggregateTest extends AbstractListenerTestCase
{
    use EventListenerIntrospectionTrait;

    /**
     * @var DefaultListenerAggregate
     */
    protected $defaultListeners;

    public function setUp()
    {
        $this->defaultListeners = new DefaultListenerAggregate(
            new ListenerOptions([
                'module_paths'         => [
                    realpath(__DIR__ . '/TestAsset'),
                ],
            ])
        );
    }

    public function testDefaultListenerAggregateCanAttachItself()
    {
        $moduleManager = new ModuleManager(['ListenerTestModule']);
        (new DefaultListenerAggregate)->attach($moduleManager->getEventManager());

        $events = $this->getEventsFromEventManager($moduleManager->getEventManager());
        $expectedEvents = [
            'loadModules' => [
                'Laminas\Loader\ModuleAutoloader',
                'config-pre' => 'Laminas\ModuleManager\Listener\ConfigListener',
                'config-post' => 'Laminas\ModuleManager\Listener\ConfigListener',
                'Laminas\ModuleManager\Listener\LocatorRegistrationListener',
                'Laminas\ModuleManager\ModuleManager',
            ],
            'loadModule.resolve' => [
                'Laminas\ModuleManager\Listener\ModuleResolverListener',
            ],
            'loadModule' => [
                'Laminas\ModuleManager\Listener\AutoloaderListener',
                'Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener',
                'Laminas\ModuleManager\Listener\InitTrigger',
                'Laminas\ModuleManager\Listener\OnBootstrapListener',
                'Laminas\ModuleManager\Listener\ConfigListener',
                'Laminas\ModuleManager\Listener\LocatorRegistrationListener',
            ],
        ];
        foreach ($expectedEvents as $event => $expectedListeners) {
            $this->assertContains($event, $events);
            $count = 0;
            foreach ($this->getListenersForEvent($event, $moduleManager->getEventManager()) as $listener) {
                if (is_array($listener)) {
                    $listener = $listener[0];
                }
                $listenerClass = get_class($listener);
                $this->assertContains($listenerClass, $expectedListeners);
                $count += 1;
            }

            $this->assertSame(count($expectedListeners), $count);
        }
    }

    public function testDefaultListenerAggregateCanDetachItself()
    {
        $listenerAggregate = new DefaultListenerAggregate;
        $moduleManager     = new ModuleManager(['ListenerTestModule']);
        $events            = $moduleManager->getEventManager();

        $this->assertEquals(1, count($this->getEventsFromEventManager($events)));

        $listenerAggregate->attach($events);
        $this->assertEquals(4, count($this->getEventsFromEventManager($events)));

        $listenerAggregate->detach($events);
        $this->assertEquals(1, count($this->getEventsFromEventManager($events)));
    }

    public function testDefaultListenerAggregateSkipsAutoloadingListenersIfLaminasLoaderIsNotUsed()
    {
        $moduleManager = new ModuleManager(['ListenerTestModule']);
        $eventManager = $moduleManager->getEventManager();
        $listenerAggregate = new DefaultListenerAggregate(new ListenerOptions([
            'use_laminas_loader' => false,
        ]));
        $listenerAggregate->attach($eventManager);

        $events = $this->getEventsFromEventManager($eventManager);
        $expectedEvents = [
            'loadModules' => [
                'config-pre' => 'Laminas\ModuleManager\Listener\ConfigListener',
                'config-post' => 'Laminas\ModuleManager\Listener\ConfigListener',
                'Laminas\ModuleManager\Listener\LocatorRegistrationListener',
                'Laminas\ModuleManager\ModuleManager',
            ],
            'loadModule.resolve' => [
                'Laminas\ModuleManager\Listener\ModuleResolverListener',
            ],
            'loadModule' => [
                'Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener',
                'Laminas\ModuleManager\Listener\InitTrigger',
                'Laminas\ModuleManager\Listener\OnBootstrapListener',
                'Laminas\ModuleManager\Listener\ConfigListener',
                'Laminas\ModuleManager\Listener\LocatorRegistrationListener',
            ],
        ];
        foreach ($expectedEvents as $event => $expectedListeners) {
            $this->assertContains($event, $events);
            $count = 0;
            foreach ($this->getListenersForEvent($event, $eventManager) as $listener) {
                if (is_array($listener)) {
                    $listener = $listener[0];
                }
                $listenerClass = get_class($listener);
                $this->assertContains($listenerClass, $expectedListeners);
                $count += 1;
            }
            $this->assertSame(count($expectedListeners), $count);
        }
    }
}
