<?php

namespace NotAutoloaderModule;

class Module
{
    public $getAutoloaderConfigCalled = false;

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
}
