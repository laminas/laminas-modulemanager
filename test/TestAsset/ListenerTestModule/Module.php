<?php

namespace ListenerTestModule;

use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\LocatorRegisteredInterface;

class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    LocatorRegisteredInterface
{
    public $initCalled = false;
    public $getConfigCalled = false;
    public $getAutoloaderConfigCalled = false;
    public $onBootstrapCalled = false;

    public function init($moduleManager = null)
    {
        $this->initCalled = true;
    }

    public function getConfig()
    {
        $this->getConfigCalled = true;
        return [
            'listener' => 'test'
        ];
    }

    public function getAutoloaderConfig()
    {
        $this->getAutoloaderConfigCalled = true;
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'Foo' => __DIR__ . '/src/Foo',
                ],
            ],
        ];
    }

    public function onBootstrap(EventInterface $e)
    {
        $this->onBootstrapCalled = true;
    }
}
