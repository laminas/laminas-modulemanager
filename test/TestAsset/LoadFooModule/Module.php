<?php

declare(strict_types=1);

namespace LoadFooModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->loadModule('LoadBarModule');
    }

    public function getConfig(): array
    {
        return [
            'bar' => 'foo',
            'foo' => 'foo',
        ];
    }
}
