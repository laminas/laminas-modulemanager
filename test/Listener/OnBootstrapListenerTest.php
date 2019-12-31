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

/**
 * @covers Laminas\ModuleManager\Listener\AbstractListener
 * @covers Laminas\ModuleManager\Listener\OnBootstrapListener
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

    public function setUp()
    {
        $sharedEvents = new SharedEventManager();
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->getEventManager()->setSharedManager($sharedEvents);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener, 1000);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, new OnBootstrapListener, 1000);

        $this->application = new MockApplication;
        $events            = new EventManager(['Laminas\Mvc\Application', 'LaminasTest\Module\TestAsset\MockApplication', 'application']);
        $events->setSharedManager($sharedEvents);
        $this->application->setEventManager($events);
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
