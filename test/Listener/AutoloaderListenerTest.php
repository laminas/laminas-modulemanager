<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\AutoloaderListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;

use function class_exists;

/**
 * @covers \Laminas\ModuleManager\Listener\AbstractListener
 * @covers \Laminas\ModuleManager\Listener\AutoloaderListener
 */
class AutoloaderListenerTest extends AbstractListenerTestCase
{
    /** @var ModuleManager */
    protected $moduleManager;

    protected function setUp(): void
    {
        $this->moduleManager = new ModuleManager([]);
        $events              = $this->moduleManager->getEventManager();
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener(), 1000);
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE, new AutoloaderListener(), 2000);
    }

    public function testAutoloadersRegisteredByAutoloaderListener()
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['ListenerTestModule']);
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        self::assertTrue($modules['ListenerTestModule']->getAutoloaderConfigCalled);
        self::assertTrue(class_exists('Foo\Bar'));
    }

    // @codingStandardsIgnoreStart
    public function testAutoloadersRegisteredIfModuleDoesNotInheritAutoloaderProviderInterfaceButDefinesGetAutoloaderConfigMethod()
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['NotAutoloaderModule']);
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        self::assertTrue($modules['NotAutoloaderModule']->getAutoloaderConfigCalled);
        self::assertTrue(class_exists('Foo\Bar'));
    }
   // @codingStandardsIgnoreEnd
}
