<?php

namespace LoadBarModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->loadModule('LoadFooModule');
    }

    public function getConfig()
    {
        return [
            'bar' => 'bar',
            'foo' => 'bar',
        ];
    }
}
