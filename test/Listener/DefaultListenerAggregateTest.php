<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\Loader\ModuleAutoloader;
use Laminas\ModuleManager\Listener\AutoloaderListener;
use Laminas\ModuleManager\Listener\ConfigListener;
use Laminas\ModuleManager\Listener\DefaultListenerAggregate;
use Laminas\ModuleManager\Listener\InitTrigger;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\Listener\LocatorRegistrationListener;
use Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\Listener\OnBootstrapListener;
use Laminas\ModuleManager\ModuleManager;

use function count;
use function get_class;
use function is_array;
use function realpath;

/**
 * @covers \Laminas\ModuleManager\Listener\AbstractListener
 * @covers \Laminas\ModuleManager\Listener\DefaultListenerAggregate
 */
class DefaultListenerAggregateTest extends AbstractListenerTestCase
{
    use EventListenerIntrospectionTrait;

    /** @var DefaultListenerAggregate */
    protected $defaultListeners;

    protected function setUp(): void
    {
        $this->defaultListeners = new DefaultListenerAggregate(
            new ListenerOptions([
                'module_paths' => [
                    realpath(__DIR__ . '/TestAsset'),
                ],
            ])
        );
    }

    public function testDefaultListenerAggregateCanAttachItself()
    {
        $moduleManager = new ModuleManager(['ListenerTestModule']);
        (new DefaultListenerAggregate())->attach($moduleManager->getEventManager());

        $events         = $this->getEventsFromEventManager($moduleManager->getEventManager());
        $expectedEvents = [
            'loadModules'        => [
                ModuleAutoloader::class,
                'config-pre'  => ConfigListener::class,
                'config-post' => ConfigListener::class,
                LocatorRegistrationListener::class,
                ModuleManager::class,
            ],
            'loadModule.resolve' => [
                ModuleResolverListener::class,
            ],
            'loadModule'         => [
                AutoloaderListener::class,
                ModuleDependencyCheckerListener::class,
                InitTrigger::class,
                OnBootstrapListener::class,
                ConfigListener::class,
                LocatorRegistrationListener::class,
            ],
        ];
        foreach ($expectedEvents as $event => $expectedListeners) {
            self::assertContains($event, $events);
            $count = 0;
            foreach ($this->getListenersForEvent($event, $moduleManager->getEventManager()) as $listener) {
                if (is_array($listener)) {
                    $listener = $listener[0];
                }
                $listenerClass = get_class($listener);
                self::assertContains($listenerClass, $expectedListeners);
                $count += 1;
            }

            self::assertSame(count($expectedListeners), $count);
        }
    }

    public function testDefaultListenerAggregateCanDetachItself()
    {
        $listenerAggregate = new DefaultListenerAggregate();
        $moduleManager     = new ModuleManager(['ListenerTestModule']);
        $events            = $moduleManager->getEventManager();

        self::assertEquals(1, count($this->getEventsFromEventManager($events)));

        $listenerAggregate->attach($events);
        self::assertEquals(4, count($this->getEventsFromEventManager($events)));

        $listenerAggregate->detach($events);
        self::assertEquals(1, count($this->getEventsFromEventManager($events)));
    }

    public function testDefaultListenerAggregateSkipsAutoloadingListenersIfLaminasLoaderIsNotUsed()
    {
        $moduleManager     = new ModuleManager(['ListenerTestModule']);
        $eventManager      = $moduleManager->getEventManager();
        $listenerAggregate = new DefaultListenerAggregate(new ListenerOptions([
            'use_laminas_loader' => false,
        ]));
        $listenerAggregate->attach($eventManager);

        $events         = $this->getEventsFromEventManager($eventManager);
        $expectedEvents = [
            'loadModules'        => [
                'config-pre'  => ConfigListener::class,
                'config-post' => ConfigListener::class,
                LocatorRegistrationListener::class,
                ModuleManager::class,
            ],
            'loadModule.resolve' => [
                ModuleResolverListener::class,
            ],
            'loadModule'         => [
                ModuleDependencyCheckerListener::class,
                InitTrigger::class,
                OnBootstrapListener::class,
                ConfigListener::class,
                LocatorRegistrationListener::class,
            ],
        ];
        foreach ($expectedEvents as $event => $expectedListeners) {
            self::assertContains($event, $events);
            $count = 0;
            foreach ($this->getListenersForEvent($event, $eventManager) as $listener) {
                if (is_array($listener)) {
                    $listener = $listener[0];
                }
                $listenerClass = get_class($listener);
                self::assertContains($listenerClass, $expectedListeners);
                $count += 1;
            }
            self::assertSame(count($expectedListeners), $count);
        }
    }
}
