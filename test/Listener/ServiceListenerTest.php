<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use ArrayObject;
use Laminas\ModuleManager\Listener\ConfigListener;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ServiceManager\Config as ServiceConfig;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

/**
 * @covers Laminas\ModuleManager\Listener\ServiceListener
 */
class ServiceListenerTest extends TestCase
{
    /**
     * @var ConfigListener
     */
    protected $configListener;

    /**
     * @var ModuleEvent
     */
    protected $event;

    /**
     * @var ServiceListener
     */
    protected $listener;

    protected $serviceManagerProps = [
        'invokableClasses',
        'factories',
        'abstractFactories',
        'shared',
        'instances',
        'aliases',
        'initializers',
        'peeringServiceManagers',
    ];

    /**
     * @var ServiceManager
     */
    protected $services;

    public function setUp()
    {
        $this->services = new ServiceManager();
        $this->listener = new ServiceListener($this->services);
        $this->listener->addServiceManager($this->services, 'service_manager', 'Laminas\ModuleManager\Feature\ServiceProviderInterface', 'getServiceConfig');
        $this->event    = new ModuleEvent();
        $this->configListener = new ConfigListener();
        $this->event->setConfigListener($this->configListener);
    }

    public function testPassingInvalidModuleDoesNothing()
    {
        $module = new stdClass();
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);

        foreach ($this->serviceManagerProps as $prop) {
            $this->assertAttributeEquals([], $prop, $this->services);
        }
    }

    public function testInvalidReturnFromModuleDoesNothing()
    {
        $module = new TestAsset\ServiceInvalidReturnModule();
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);

        foreach ($this->serviceManagerProps as $prop) {
            $this->assertAttributeEquals([], $prop, $this->services);
        }
    }

    public function getServiceConfig()
    {
        return [
            'invokables' => [__CLASS__ => __CLASS__],
            'factories' => [
                'foo' => function ($sm) { },
            ],
            'abstract_factories' => [
                new \Laminas\ServiceManager\Di\DiAbstractServiceFactory(new \Laminas\Di\Di()),
            ],
            'shared' => [
                'foo' => false,
                'laminastestmodulemanagerlistenerservicelistenertest' => true,
            ],
            'aliases'  => [
                'bar' => 'foo',
            ],
        ];
    }

    public function assertServiceManagerIsConfigured()
    {
        $this->listener->onLoadModulesPost($this->event);
        foreach ($this->getServiceConfig() as $prop => $expected) {
            if ($prop == 'invokables') {
                $prop = 'invokableClasses';
                foreach ($expected as $key => $value) {
                    $normalized = strtolower($key);
                    $normalized = str_replace(['\\', '_'], '', $normalized);
                    unset($expected[$key]);
                    $expected[$normalized] = $value;
                }
            }
            if ($prop == 'abstract_factories') {
                $prop = 'abstractFactories';
            }
            $this->assertAttributeEquals($expected, $prop, $this->services, "$prop assertion failed");
        }
    }

    public function testModuleReturningArrayConfiguresServiceManager()
    {
        $config = $this->getServiceConfig();
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);
        $this->assertServiceManagerIsConfigured();
    }

    public function testModuleReturningTraversableConfiguresServiceManager()
    {
        $config = $this->getServiceConfig();
        $config = new ArrayObject($config);
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);
        $this->assertServiceManagerIsConfigured();
    }

    public function testModuleServiceConfigOverridesGlobalConfig()
    {
        $this->listener = new ServiceListener($this->services, ['aliases' => ['foo' => 'bar']]);
        $this->listener->addServiceManager($this->services, 'service_manager', 'Laminas\ModuleManager\Feature\ServiceProviderInterface', 'getServiceConfig');
        $config = ['aliases' => ['foo' => 'baz']];
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);
        $this->listener->onLoadModulesPost($this->event);
        $this->assertAttributeEquals($config['aliases'], 'aliases', $this->services, "aliases assertion failed - module config did not override main config");
    }

    public function testModuleReturningServiceConfigConfiguresServiceManager()
    {
        $config = $this->getServiceConfig();
        $config = new ServiceConfig($config);
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);
        $this->assertServiceManagerIsConfigured();
    }

    public function testMergedConfigContainingServiceManagerKeyWillConfigureServiceManagerPostLoadModules()
    {
        $config = ['service_manager' => $this->getServiceConfig()];
        $configListener = new ConfigListener();
        $configListener->setMergedConfig($config);
        $this->event->setConfigListener($configListener);
        $this->assertServiceManagerIsConfigured();
    }
}
