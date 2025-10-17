<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Override;

final class CustomPluginManagerFactory implements FactoryInterface
{
    /** @var null|array */
    protected $creationOptions;

    /**
     * Create and return an instance of the CustomPluginManager (v3)
     *
     * {@inheritDoc}
     */
    #[Override]
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): CustomPluginManager
    {
        $options = $options ?: [];
        return new CustomPluginManager($container, $options);
    }

    /**
     * Create and return an instance of the CustomPluginManager (v2)
     *
     * {@inheritDoc}
     */
    #[Override]
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, CustomPluginManager::class, $this->creationOptions);
    }

    /**
     * Provide options to use during instantiation (v2).
     */
    public function setCreationOptions(array $options): void
    {
        $this->creationOptions = $options;
    }
}
