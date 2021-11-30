<?php

declare(strict_types=1);

namespace ListenerTestModule;

use Laminas\EventManager\EventInterface;
use Laminas\Loader\StandardAutoloader;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\LocatorRegisteredInterface;

class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    LocatorRegisteredInterface
{
    /** @var bool */
    public $initCalled = false;
    /** @var bool */
    public $getConfigCalled = false;
    /** @var bool */
    public $getAutoloaderConfigCalled = false;
    /** @var bool */
    public $onBootstrapCalled = false;

    /** @param mixed|null $moduleManager */
    public function init($moduleManager = null): void
    {
        $this->initCalled = true;
    }

    /**
     * @return string[]
     * @psalm-return array<string, string>
     */
    public function getConfig(): array
    {
        $this->getConfigCalled = true;
        return [
            'listener' => 'test',
        ];
    }

    public function getAutoloaderConfig(): array
    {
        $this->getAutoloaderConfigCalled = true;
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    'Foo' => __DIR__ . '/src/Foo',
                ],
            ],
        ];
    }

    public function onBootstrap(EventInterface $e): void
    {
        $this->onBootstrapCalled = true;
    }
}
