<?php

declare(strict_types=1);

namespace LoadBarModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->loadModule('LoadFooModule');
    }

    public function getConfig(): array
    {
        return [
            'bar' => 'bar',
            'foo' => 'bar',
        ];
    }
}
