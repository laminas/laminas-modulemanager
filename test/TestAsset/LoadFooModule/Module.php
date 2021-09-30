<?php

namespace LoadFooModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->loadModule('LoadBarModule');
    }

    public function getConfig()
    {
        return [
            'bar' => 'foo',
            'foo' => 'foo',
        ];
    }
}
