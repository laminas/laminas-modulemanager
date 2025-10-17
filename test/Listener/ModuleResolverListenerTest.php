<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\AbstractListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use ListenerTestModule;
use ModuleAsClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ModuleResolverListener::class)]
#[CoversClass(AbstractListener::class)]
final class ModuleResolverListenerTest extends AbstractListenerTestCase
{
    #[DataProvider('validModuleNameProvider')]
    public function testModuleResolverListenerCanResolveModuleClasses(
        string $moduleName,
        string $expectedInstanceOf
    ): void {
        $moduleResolver = new ModuleResolverListener();
        $e              = new ModuleEvent();

        $e->setModuleName($moduleName);
        self::assertInstanceOf($expectedInstanceOf, $moduleResolver($e));
    }

    /** @return array<string, array<int, string>> */
    public static function validModuleNameProvider(): array
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
