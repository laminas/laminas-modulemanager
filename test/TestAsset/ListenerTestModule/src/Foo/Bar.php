<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

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
