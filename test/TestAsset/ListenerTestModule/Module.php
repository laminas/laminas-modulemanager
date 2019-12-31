<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace ListenerTestModule;

use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\LocatorRegisteredInterface;

/**
 * @category   Laminas
 * @package    Laminas_ModuleManager
 * @subpackage UnitTest
 */
class Module implements
    AutoloaderProviderInterface,
    LocatorRegisteredInterface,
    BootstrapListenerInterface
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
        return array(
            'listener' => 'test'
        );
    }

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

    public function onBootstrap(EventInterface $e)
    {
        $this->onBootstrapCalled = true;
    }
}
