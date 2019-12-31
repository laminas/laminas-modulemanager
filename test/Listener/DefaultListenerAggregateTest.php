<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\DefaultListenerAggregate;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\ModuleManager;

/**
 * @covers Laminas\ModuleManager\Listener\AbstractListener
 * @covers Laminas\ModuleManager\Listener\DefaultListenerAggregate
 */
class DefaultListenerAggregateTest extends AbstractListenerTestCase
{
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
        $moduleManager->getEventManager()->attachAggregate(new DefaultListenerAggregate);

        $events = $moduleManager->getEventManager()->getEvents();
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
        $moduleManager     = new ModuleManager(['ListenerTestModule']);

        $this->assertEquals(1, count($moduleManager->getEventManager()->getEvents()));

        $listenerAggregate->attach($moduleManager->getEventManager());
        $this->assertEquals(4, count($moduleManager->getEventManager()->getEvents()));

        $listenerAggregate->detach($moduleManager->getEventManager());
        $this->assertEquals(1, count($moduleManager->getEventManager()->getEvents()));
    }
}
