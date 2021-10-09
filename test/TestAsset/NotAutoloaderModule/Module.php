<?php

declare(strict_types=1);

namespace NotAutoloaderModule;

use Laminas\Loader\StandardAutoloader;

class Module
{
    /** @var bool */
    public $getAutoloaderConfigCalled = false;

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
}
