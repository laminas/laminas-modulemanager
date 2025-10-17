<?php

declare(strict_types=1);

namespace LoadBarModule;

use Laminas\ModuleManager\ModuleManager;

final class Module
{
    public function init(ModuleManager $moduleManager): void
    {
        $moduleManager->loadModule('LoadFooModule');
    }

    /**
     * @return string[]
     * @psalm-return array<string, string>
     */
    public function getConfig(): array
    {
        return [
            'bar' => 'bar',
            'foo' => 'bar',
        ];
    }
}
