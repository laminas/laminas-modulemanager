<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace NotAutoloaderModule;

class Module
{
    public $getAutoloaderConfigCalled = false;

    public function getAutoloaderConfig()
    {
        $this->getAutoloaderConfigCalled = true;
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'Foo' => __DIR__ . '/src/Foo',
                ),
            ),
        );
    }
}
