<?php

declare(strict_types=1);

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
    /** @dataProvider validModuleNameProvider */
    public function testModuleResolverListenerCanResolveModuleClasses(
        string $moduleName,
        string $expectedInstanceOf
    ): void {
        $moduleResolver = new ModuleResolverListener();
        $e              = new ModuleEvent();

        $e->setModuleName($moduleName);
        self::assertInstanceOf($expectedInstanceOf, $moduleResolver($e));
    }

    public function validModuleNameProvider(): array
    {
        return [
            // Description => [module name, expectedInstanceOf]
            'Append Module'  => ['ListenerTestModule', ListenerTestModule\Module::class],
            'FQCN Module'    => [ListenerTestModule\Module::class, ListenerTestModule\Module::class],
            'FQCN Arbitrary' => [ListenerTestModule\FooModule::class, ListenerTestModule\FooModule::class],
        ];
    }

    public function testModuleResolverListenerReturnFalseIfCannotResolveModuleClasses(): void
    {
        $moduleResolver = new ModuleResolverListener();
        $e              = new ModuleEvent();

        $e->setModuleName('DoesNotExist');
        self::assertFalse($moduleResolver($e));
    }

    public function testModuleResolverListenerPrefersModuleClassesInModuleNamespaceOverNamedClasses(): void
    {
        $moduleResolver = new ModuleResolverListener();
        $e              = new ModuleEvent();

        $e->setModuleName('ModuleAsClass');
        self::assertInstanceOf(ModuleAsClass\Module::class, $moduleResolver($e));
    }

    public function testModuleResolverListenerWillNotAttemptToResolveModuleAsClassNameGenerator(): void
    {
        $moduleResolver = new ModuleResolverListener();
        $e              = new ModuleEvent();

        $e->setModuleName('Generator');
        self::assertFalse($moduleResolver($e));
    }
}
