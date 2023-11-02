<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use stdClass;

class StdClassWithModuleDependencies extends stdClass
{
    public function getModuleDependencies(): array
    {
        return [];
    }
}
