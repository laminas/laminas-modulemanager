<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Override;
use stdClass;

final class SampleAbstractFactory implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    #[Override]
    public function canCreate(ContainerInterface $container, $name)
    {
        return true;
    }

    /** {@inheritDoc} */
    #[Override]
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return true;
    }

    /** {@inheritDoc} */
    #[Override]
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        return new stdClass();
    }

    /** {@inheritDoc} */
    #[Override]
    public function createServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this($container, '');
    }
}
