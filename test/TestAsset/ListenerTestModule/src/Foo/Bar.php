<?php

declare(strict_types=1);

namespace Foo;

use Laminas\ModuleManager\ModuleManager;
use ListenerTestModule\Module;

class Bar
{
    public function __construct(public Module $module, public ModuleManager $moduleManager)
    {
    }
}
