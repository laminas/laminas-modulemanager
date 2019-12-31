<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\Listener\OnBootstrapListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use LaminasTest\ModuleManager\TestAsset\MockApplication;
use ReflectionClass;

/**
 * @covers \Laminas\ModuleManager\Listener\AbstractListener
 * @covers \Laminas\ModuleManager\Listener\OnBootstrapListener
 */
class OnBootstrapListenerTest extends AbstractListenerTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    protected function setUp()
    {
        $sharedEvents = new SharedEventManager();
        $events       = new EventManager($sharedEvents);
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->setEventManager($events);

        $events->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener, 1000);
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE, new OnBootstrapListener, 1000);

        $this->application = new MockApplication;
        $appEvents         = $this->createEventManager($sharedEvents);
        $appEvents->setIdentifiers([
            'Laminas\Mvc\Application',
            'LaminasTest\Module\TestAsset\MockApplication',
            'application',
        ]);

        $this->application->setEventManager($appEvents);
    }

    public function createEventManager(SharedEventManager $sharedEvents)
    {
        $r = new ReflectionClass(EventManager::class);
        if ($r->hasMethod('setSharedManager')) {
            $events = new EventManager();
            $events->setSharedManager($sharedEvents);
            return $events;
        }

        return new EventManager($sharedEvents);
    }

    public function testOnBootstrapMethodCalledByOnBootstrapListener()
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['ListenerTestModule']);
        $moduleManager->loadModules();
        $this->application->bootstrap();
        $modules = $moduleManager->getLoadedModules();
        $this->assertTrue($modules['ListenerTestModule']->onBootstrapCalled);
    }
}
