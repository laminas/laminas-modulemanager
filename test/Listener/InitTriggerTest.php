<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\InitTrigger;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;

/**
 * @covers \Laminas\ModuleManager\Listener\AbstractListener
 * @covers \Laminas\ModuleManager\Listener\InitTrigger
 */
class InitTriggerTest extends AbstractListenerTestCase
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    protected function setUp()
    {
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener,
            1000
        );
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, new InitTrigger, 2000);
    }

    public function testInitMethodCalledByInitTriggerListener()
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['ListenerTestModule']);
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        $this->assertTrue($modules['ListenerTestModule']->initCalled);
    }
}
