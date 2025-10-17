<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use stdClass;

/**
 * @psalm-suppress ClassMustBeFinal Test asset used with mocking.
 */
class StdClassWithModuleDependencies extends stdClass
{
    public function getModuleDependencies(): array
    {
        return [];
    }
}
