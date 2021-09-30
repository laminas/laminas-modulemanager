<?php

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use stdClass;

class SampleAbstractFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $name)
    {
        return true;
    }

    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return true;
    }

    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new stdClass;
    }

    public function createServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this($container, '');
    }
}
