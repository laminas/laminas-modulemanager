<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use stdClass;

class SampleAbstractFactory implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function canCreate(ContainerInterface $container, $name)
    {
        return true;
    }

    /** {@inheritDoc} */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return true;
    }

    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        return new stdClass();
    }

    /** {@inheritDoc} */
    public function createServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this($container, '');
    }
}
