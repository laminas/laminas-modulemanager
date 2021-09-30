<?php

namespace Foo;

use Laminas\ModuleManager\ModuleManager;
use ListenerTestModule\Module;

class Bar
{
    public $module;
    public $moduleManager;

    public function __construct(Module $module, ModuleManager $moduleManager)
    {
        $this->module        = $module;
        $this->moduleManager = $moduleManager;
    }
}
