<?php

namespace LoadOtherModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->loadModule('BarModule');
        $moduleManager->loadModule('BazModule');
    }

    public function getConfig()
    {
        return ['loaded' => 'oh, yeah baby!'];
    }
}
