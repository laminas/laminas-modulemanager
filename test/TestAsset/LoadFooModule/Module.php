<?php

declare(strict_types=1);

namespace LoadFooModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager): void
    {
        $moduleManager->loadModule('LoadBarModule');
    }

    /**
     * @return string[]
     * @psalm-return array<string, string>
     */
    public function getConfig(): array
    {
        return [
            'bar' => 'foo',
            'foo' => 'foo',
        ];
    }
}
