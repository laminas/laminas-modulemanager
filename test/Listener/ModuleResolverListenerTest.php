<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use ListenerTestModule;
use ModuleAsClass;

/**
 * @covers \Laminas\ModuleManager\Listener\AbstractListener
 * @covers \Laminas\ModuleManager\Listener\ModuleResolverListener
 */
class ModuleResolverListenerTest extends AbstractListenerTestCase
{
    /**
     * @dataProvider validModuleNameProvider
     */
    public function testModuleResolverListenerCanResolveModuleClasses($moduleName, $expectedInstanceOf)
    {
        $moduleResolver = new ModuleResolverListener;
        $e = new ModuleEvent;

        $e->setModuleName($moduleName);
        self::assertInstanceOf($expectedInstanceOf, $moduleResolver($e));
    }

    public function validModuleNameProvider()
    {
        return [
            // Description => [module name, expectedInstanceOf]
            'Append Module'  => ['ListenerTestModule', ListenerTestModule\Module::class],
            'FQCN Module'    => [ListenerTestModule\Module::class, ListenerTestModule\Module::class],
            'FQCN Arbitrary' => [ListenerTestModule\FooModule::class, ListenerTestModule\FooModule::class],
        ];
    }

    public function testModuleResolverListenerReturnFalseIfCannotResolveModuleClasses()
    {
        $moduleResolver = new ModuleResolverListener;
        $e = new ModuleEvent;

        $e->setModuleName('DoesNotExist');
        self::assertFalse($moduleResolver($e));
    }

    public function testModuleResolverListenerPrefersModuleClassesInModuleNamespaceOverNamedClasses()
    {
        $moduleResolver = new ModuleResolverListener;
        $e = new ModuleEvent;

        $e->setModuleName('ModuleAsClass');
        self::assertInstanceOf(ModuleAsClass\Module::class, $moduleResolver($e));
    }

    public function testModuleResolverListenerWillNotAttemptToResolveModuleAsClassNameGenerator()
    {
        $moduleResolver = new ModuleResolverListener;
        $e = new ModuleEvent;

        $e->setModuleName('Generator');
        self::assertFalse($moduleResolver($e));
    }
}
