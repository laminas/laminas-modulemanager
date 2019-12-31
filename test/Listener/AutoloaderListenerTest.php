<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\AutoloaderListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;

/**
 * @covers Laminas\ModuleManager\Listener\AbstractListener
 * @covers Laminas\ModuleManager\Listener\AutoloaderListener
 */
class AutoloaderListenerTest extends AbstractListenerTestCase
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    public function setUp()
    {
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener, 1000);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, new AutoloaderListener, 2000);
    }

    public function testAutoloadersRegisteredByAutoloaderListener()
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['ListenerTestModule']);
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        $this->assertTrue($modules['ListenerTestModule']->getAutoloaderConfigCalled);
        $this->assertTrue(class_exists('Foo\Bar'));
    }

    public function testAutoloadersRegisteredIfModuleDoesNotInheritAutoloaderProviderInterfaceButDefinesGetAutoloaderConfigMethod()
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['NotAutoloaderModule']);
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        $this->assertTrue($modules['NotAutoloaderModule']->getAutoloaderConfigCalled);
        $this->assertTrue(class_exists('Foo\Bar'));
    }
}
