<?php

declare(strict_types=1);

namespace Foo;

use Laminas\ModuleManager\ModuleManager;
use ListenerTestModule\Module;

class Bar
{
    /** @var Module */
    public $module;

    /** @var ModuleManager */
    public $moduleManager;

    public function __construct(Module $module, ModuleManager $moduleManager)
    {
        $this->module        = $module;
        $this->moduleManager = $moduleManager;
    }
}
