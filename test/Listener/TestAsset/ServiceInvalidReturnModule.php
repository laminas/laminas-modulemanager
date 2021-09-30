<?php

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use stdClass;

class ServiceInvalidReturnModule
{
    public function getServiceConfiguration()
    {
        return new stdClass;
    }
}
