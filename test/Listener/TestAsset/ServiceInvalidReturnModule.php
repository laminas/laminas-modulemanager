<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use stdClass;

class ServiceInvalidReturnModule
{
    public function getServiceConfiguration(): stdClass
    {
        return new stdClass();
    }
}
