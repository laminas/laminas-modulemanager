<?php

declare(strict_types=1);

namespace LoadOtherModule;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function init(ModuleManager $moduleManager): void
    {
        $moduleManager->loadModule('BarModule');
        $moduleManager->loadModule('BazModule');
    }

    /**
     * @return string[]
     * @psalm-return array<string, string>
     */
    public function getConfig(): array
    {
        return ['loaded' => 'oh, yeah baby!'];
    }
}
