<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Exception;
use Laminas\ModuleManager\Feature;
use Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase as TestCase;
use stdClass;

/**
 * @covers Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener
 */
class ModuleDependencyCheckerListenerTest extends TestCase
{
    /**
     * @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke
     */
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

    /**
     * @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke
     */
    public function testCallsGetModuleDependenciesOnModuleNotImplementingInterface()
    {
        $module = $this->getMockBuilder(stdClass::class)->setMethods(['getModuleDependencies'])->getMock();
        $module->expects($this->once())->method('getModuleDependencies')->will($this->returnValue([]));

        $event = $this->getMockBuilder(ModuleEvent::class)->getMock();
        $event->expects($this->any())->method('getModule')->will($this->returnValue($module));

        $listener = new ModuleDependencyCheckerListener();
        $listener->__invoke($event);
    }

    /**
     * @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke
     */
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
}
