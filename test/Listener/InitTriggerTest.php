<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\AbstractListener;
use Laminas\ModuleManager\Listener\InitTrigger;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InitTrigger::class)]
#[CoversClass(AbstractListener::class)]
final class InitTriggerTest extends AbstractListenerTestCase
{
    /** @var ModuleManager */
    protected $moduleManager;

    #[Override]
    protected function setUp(): void
    {
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener(),
            1000
        );
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, new InitTrigger(), 2000);
    }

    public function testInitMethodCalledByInitTriggerListener(): void
    {
        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['ListenerTestModule']);
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        self::assertTrue($modules['ListenerTestModule']->initCalled);
    }
}
