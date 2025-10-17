<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Exception;
use Laminas\ModuleManager\Feature;
use Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener;
use Laminas\ModuleManager\ModuleEvent;
use LaminasTest\ModuleManager\Listener\TestAsset\StdClassWithModuleDependencies;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ModuleDependencyCheckerListener::class)]
final class ModuleDependencyCheckerListenerTest extends TestCase
{
    public function testCallsGetModuleDependenciesOnModuleImplementingInterface(): void
    {
        //$moduleManager = new ModuleManager(array());
        /*$moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE,
            new ModuleDependencyCheckerListener(),
            2000
        ); */

        $module = $this->getMockBuilder(Feature\DependencyIndicatorInterface::class)->getMock();
        $module->expects(self::once())->method('getModuleDependencies')->willReturn([]);

        $event = $this->getMockBuilder(ModuleEvent::class)->getMock();
        $event->expects(self::any())->method('getModule')->willReturn($module);

        $listener = new ModuleDependencyCheckerListener();
        $listener->__invoke($event);
    }

    public function testCallsGetModuleDependenciesOnModuleNotImplementingInterface(): void
    {
        $module = $this->getMockBuilder(StdClassWithModuleDependencies::class)->getMock();
        $module->expects(self::once())->method('getModuleDependencies')->willReturn([]);

        $event = $this->getMockBuilder(ModuleEvent::class)->getMock();
        $event->expects(self::any())->method('getModule')->willReturn($module);

        $listener = new ModuleDependencyCheckerListener();
        $listener->__invoke($event);
    }

    public function testNotFulfilledDependencyThrowsException(): void
    {
        $module = $this->getMockBuilder(StdClassWithModuleDependencies::class)->getMock();
        $module->expects(self::once())->method('getModuleDependencies')->willReturn(['OtherModule']);

        $event = $this->getMockBuilder(ModuleEvent::class)->getMock();
        $event->expects(self::any())->method('getModule')->willReturn($module);

        $listener = new ModuleDependencyCheckerListener();
        $this->expectException(Exception\MissingDependencyModuleException::class);
        $listener->__invoke($event);
    }
}
