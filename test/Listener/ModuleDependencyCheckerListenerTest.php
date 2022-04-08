<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use DependencyModule\Module;
use Laminas\ModuleManager\Exception;
use Laminas\ModuleManager\Feature;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener
 */
class ModuleDependencyCheckerListenerTest extends TestCase
{
    /** @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke */
    public function testCallsGetModuleDependenciesOnModuleImplementingInterface()
    {
        //$moduleManager = new ModuleManager(array());
        /*$moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE,
            new ModuleDependencyCheckerListener(),
            2000
        ); */

        $module = $this->getMockBuilder(Feature\DependencyIndicatorInterface::class)->getMock();
        $module->expects($this->once())->method('getModuleDependencies')->will($this->returnValue([]));

        $event = $this->getMockBuilder(ModuleEvent::class)->getMock();
        $event->expects($this->any())->method('getModule')->will($this->returnValue($module));

        $listener = new ModuleDependencyCheckerListener();
        $listener->__invoke($event);
    }

    /** @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke */
    public function testCallsGetModuleDependenciesOnModuleNotImplementingInterface()
    {
        $module = $this->getMockBuilder(stdClass::class)->setMethods(['getModuleDependencies'])->getMock();
        $module->expects($this->once())->method('getModuleDependencies')->will($this->returnValue([]));

        $event = $this->getMockBuilder(ModuleEvent::class)->getMock();
        $event->expects($this->any())->method('getModule')->will($this->returnValue($module));

        $listener = new ModuleDependencyCheckerListener();
        $listener->__invoke($event);
    }

    /** @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke */
    public function testNotFulfilledDependencyThrowsException()
    {
        $module = $this->getMockBuilder(stdClass::class)->setMethods(['getModuleDependencies'])->getMock();
        $module->expects($this->once())->method('getModuleDependencies')->will($this->returnValue(['OtherModule']));

        $event = $this->getMockBuilder(ModuleEvent::class)->getMock();
        $event->expects($this->any())->method('getModule')->will($this->returnValue($module));

        $listener = new ModuleDependencyCheckerListener();
        $this->expectException(Exception\MissingDependencyModuleException::class);
        $listener->__invoke($event);
    }

    /** @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke */
    public function testNotFulfilledDependencyThrowsExceptionViaEvent()
    {
        $listenerOptions = new ListenerOptions();

        $moduleManager = new ModuleManager([]);
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener(),
            1000
        );
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, new ModuleDependencyCheckerListener($listenerOptions), 2000);

        $this->expectException(Exception\MissingDependencyModuleException::class);

        $moduleManager->loadModule(Module::class);
    }

    /** @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke */
    public function testNotFulfilledDependencyIsLoadedViaEvent()
    {
        $listenerOptions = new ListenerOptions();
        $listenerOptions->setLoadDependencies(true);

        $moduleManager = new ModuleManager([]);
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener(),
            1000
        );
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, new ModuleDependencyCheckerListener($listenerOptions), 2000);
        $moduleManager->loadModule(Module::class);

        $loadedModules = $moduleManager->getLoadedModules();

        self::assertCount(2, $loadedModules);

        self::assertArrayHasKey('DependencyModule\Module', $loadedModules);
        self::assertArrayHasKey('SomeModule\Module', $loadedModules);
    }
}
