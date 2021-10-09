<?php

declare(strict_types=1);

namespace LoadOtherModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->loadModule('BarModule');
        $moduleManager->loadModule('BazModule');
    }

    public function getConfig(): array
    {
        return ['loaded' => 'oh, yeah baby!'];
    }
}
