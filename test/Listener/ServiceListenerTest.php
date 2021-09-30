<?php

namespace LaminasTest\ModuleManager\Listener;

use ArrayObject;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\ModuleManager\Exception;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;
use Laminas\ModuleManager\Listener\ConfigListener;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ServiceManager\Config as ServiceConfig;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

use function array_keys;
use function sprintf;

/**
 * @covers \Laminas\ModuleManager\Listener\ServiceListener
 */
class ServiceListenerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    /**
     * @var ConfigListener
     */
    protected $configListener;

    protected $defaultServiceConfig = [
        'abstract_factories' => [],
        'aliases'            => [],
        'delegators'         => [],
        'factories'          => [],
        'initializers'       => [],
        'invokables'         => [],
        'lazy_services'      => [],
        'services'           => [],
        'shared'             => [],
    ];

    /**
     * @var ModuleEvent
     */
    protected $event;

    /**
     * @var ServiceListener
     */
    protected $listener;

    /**
     * @var ServiceManager
     */
    protected $services;

    protected function setUp() : void
    {
        $this->services = new ServiceManager();
        $this->listener = new ServiceListener($this->services);
        $this->listener->addServiceManager(
            $this->services,
            'service_manager',
            ServiceProviderInterface::class,
            'getServiceConfig'
        );

        $this->event          = new ModuleEvent();
        $this->configListener = new ConfigListener();
        $this->event->setConfigListener($this->configListener);
    }

    public function getServiceConfig()
    {
        // @codingStandardsIgnoreStart
        return [
            'invokables' => [
                __CLASS__ => __CLASS__
            ],
            'factories' => [
                'foo' => function ($sm) { },
            ],
            'abstract_factories' => [
                new TestAsset\SampleAbstractFactory(),
            ],
            'shared' => [
                'foo' => false,
                'laminastestmodulemanagerlistenerservicelistenertest' => true,
            ],
            'aliases'  => [
                'bar' => 'foo',
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function getConfiguredServiceManager($listener = null)
    {
        $listener = $listener ?: $this->listener;
        $r = new ReflectionProperty($listener, 'defaultServiceManager');
        $r->setAccessible(true);
        return $r->getValue($listener);
    }

    public function assertServiceManagerConfiguration()
    {
        $this->listener->onLoadModulesPost($this->event);
        $services = $this->getConfiguredServiceManager();

        self::assertInstanceOf(ServiceManager::class, $services);
        self::assertSame($this->services, $services);

        self::assertTrue($services->has(__CLASS__));
        self::assertTrue($services->has('foo'));
        self::assertTrue($services->has('bar'));
        self::assertTrue($services->has('resolved-by-abstract'));
    }

    public function assertServicesFromConfigArePresent(array $config, ServiceManager $serviceManager)
    {
        foreach ($config as $type => $services) {
            switch ($type) {
                case 'invokables':
                    // fall through
                case 'factories':
                    // fall through
                case 'aliases':
                    foreach (array_keys($services) as $service) {
                        self::assertTrue(
                            $serviceManager->has($service),
                            sprintf(
                                'Service manager is missing expected service %s',
                                $service
                            )
                        );
                    }
                    break;
                default:
                    // Cannot test other types
                    break;
            }
        }
    }

    public function testPassingInvalidModuleDoesNothing()
    {
        $module = new stdClass();
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);

        self::assertSame($this->services, $this->getConfiguredServiceManager());
    }

    public function testInvalidReturnFromModuleDoesNothing()
    {
        $module = new TestAsset\ServiceInvalidReturnModule();
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);

        self::assertSame($this->services, $this->getConfiguredServiceManager());
    }

    public function testModuleReturningArrayConfiguresServiceManager()
    {
        $config = $this->getServiceConfig();
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);
        $services = $this->getConfiguredServiceManager();
        self::assertServiceManagerConfiguration();
    }

    public function testModuleReturningTraversableConfiguresServiceManager()
    {
        $config = $this->getServiceConfig();
        $config = new ArrayObject($config);
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);
        self::assertServiceManagerConfiguration();
    }

    public function testModuleServiceConfigOverridesGlobalConfig()
    {
        $defaultConfig = ['aliases' => ['foo' => 'bar'], 'services' => [
            'bar' => new stdClass(),
            'baz' => new stdClass(),
        ]];
        $this->listener = new ServiceListener($this->services, $defaultConfig);
        $this->listener->addServiceManager(
            $this->services,
            'service_manager',
            ServiceProviderInterface::class,
            'getServiceConfig'
        );
        $config = ['aliases' => ['foo' => 'baz']];
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->event->setModuleName(__NAMESPACE__ . '\TestAsset\ServiceProvider');
        $this->listener->onLoadModule($this->event);
        $this->listener->onLoadModulesPost($this->event);

        $services = $this->getConfiguredServiceManager();
        self::assertTrue($services->has('foo'));
        self::assertNotSame($services->get('foo'), $services->get('bar'));
        self::assertSame($services->get('foo'), $services->get('baz'));
    }

    public function testModuleReturningServiceConfigConfiguresServiceManager()
    {
        $config = $this->getServiceConfig();
        $config = new ServiceConfig($config);
        $module = new TestAsset\ServiceProviderModule($config);
        $this->event->setModule($module);
        $this->listener->onLoadModule($this->event);
        self::assertServiceManagerConfiguration();
    }

    public function testMergedConfigContainingServiceManagerKeyWillConfigureServiceManagerPostLoadModules()
    {
        $config = ['service_manager' => $this->getServiceConfig()];
        $configListener = new ConfigListener();
        $configListener->setMergedConfig($config);
        $this->event->setConfigListener($configListener);
        self::assertServiceManagerConfiguration();
    }

    public function invalidServiceManagerTypes()
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['FooBar']],
            'object'     => [(object) ['service_manager' => 'FooBar']],
        ];
    }

    /**
     * @dataProvider invalidServiceManagerTypes
     */
    public function testUsingNonStringServiceManagerWithAddServiceManagerRaisesException($serviceManager)
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('expected ServiceManager or string');
        $this->listener->addServiceManager(
            $serviceManager,
            'service_manager',
            ServiceProviderInterface::class,
            'getServiceConfig'
        );
    }

    public function testCreatesPluginManagerBasedOnModuleImplementingSpecifiedProviderInterface()
    {
        $services = $this->services;
        $services->setFactory('CustomPluginManager', TestAsset\CustomPluginManagerFactory::class);
        $listener = new ServiceListener($services);

        $listener->addServiceManager(
            'CustomPluginManager',
            'custom_plugins',
            TestAsset\CustomPluginProviderInterface::class,
            'getCustomPluginConfig'
        );

        $pluginConfig = $this->getServiceConfig();
        $module = new TestAsset\CustomPluginProviderModule($pluginConfig);
        $this->event->setModule($module);
        $listener->onLoadModule($this->event);
        $listener->onLoadModulesPost($this->event);

        $configuredServices = $this->getConfiguredServiceManager($listener);
        self::assertSame($services, $configuredServices);
        self::assertTrue($configuredServices->has('CustomPluginManager'));
        $plugins = $configuredServices->get('CustomPluginManager');
        self::assertInstanceOf(TestAsset\CustomPluginManager::class, $plugins);

        self::assertServicesFromConfigArePresent($pluginConfig, $plugins);
    }

    public function testCreatesPluginManagerBasedOnModuleDuckTypingSpecifiedProviderInterface()
    {
        $services = $this->services;
        $services->setFactory('CustomPluginManager', TestAsset\CustomPluginManagerFactory::class);
        $listener = new ServiceListener($services);

        $listener->addServiceManager(
            'CustomPluginManager',
            'custom_plugins',
            TestAsset\CustomPluginProviderInterface::class,
            'getCustomPluginConfig'
        );

        $pluginConfig = $this->getServiceConfig();
        $module = new TestAsset\CustomPluginDuckTypeProviderModule($pluginConfig);
        $this->event->setModule($module);
        $listener->onLoadModule($this->event);
        $listener->onLoadModulesPost($this->event);

        $configuredServices = $this->getConfiguredServiceManager($listener);
        self::assertSame($services, $configuredServices);
        self::assertTrue($configuredServices->has('CustomPluginManager'));
        $plugins = $configuredServices->get('CustomPluginManager');
        self::assertInstanceOf(TestAsset\CustomPluginManager::class, $plugins);

        self::assertServicesFromConfigArePresent($pluginConfig, $plugins);
    }

    public function testAttachesListenersAtExpectedPriorities()
    {
        $events = new EventManager();
        $this->listener->attach($events);
        self::assertListenerAtPriority(
            [$this->listener, 'onLoadModule'],
            1,
            ModuleEvent::EVENT_LOAD_MODULE,
            $events,
            'onLoadModule not registered at expected priority'
        );
        self::assertListenerAtPriority(
            [$this->listener, 'onLoadModulesPost'],
            1,
            ModuleEvent::EVENT_LOAD_MODULES_POST,
            $events,
            'onLoadModulesPost not registered at expected priority'
        );

        return [
            'listener' => $this->listener,
            'events'   => $events,
        ];
    }

    /**
     * @depends testAttachesListenersAtExpectedPriorities
     */
    public function testCanDetachListeners(array $dependencies)
    {
        $listener = $dependencies['listener'];
        $events   = $dependencies['events'];

        $listener->detach($events);

        $listeners = $this->getArrayOfListenersForEvent(ModuleEvent::EVENT_LOAD_MODULE, $events);
        self::assertCount(0, $listeners);
        $listeners = $this->getArrayOfListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $events);
        self::assertCount(0, $listeners);
    }

    public function testListenerCanOverrideServicesInServiceManagers()
    {
        $services = new ServiceManager();
        $services->setService('config', []);
        $services->setFactory('foo', function ($services) {
            return $services;
        });
        $listener = new ServiceListener($services);
        $listener->addServiceManager(
            $services,
            'service_manager',
            ServiceProviderInterface::class,
            'getServiceConfig'
        );

        $module = new TestAsset\ServiceProviderModule([
            'services' => [
                'config' => [ 'foo' => 'bar'],
            ],
            'factories' => [
                'foo' => function ($services) {
                    return new stdClass();
                },
            ],
        ]);

        $event          = new ModuleEvent();
        $configListener = new ConfigListener();
        $event->setConfigListener($configListener);

        $event->setModule($module);
        $listener->onLoadModule($event);
        $listener->onLoadModulesPost($event);

        self::assertTrue($services->has('config'));
        self::assertTrue($services->has('foo'));
        self::assertEquals(['foo' => 'bar'], $services->get('config'), 'Config service was not overridden');
        self::assertInstanceOf(stdClass::class, $services->get('foo'), 'Foo service was not overridden');
    }

    public function testOnLoadModulesPostShouldNotRaiseExceptionIfNamedServiceManagerDoesNotExist()
    {
        $services = new ServiceManager();
        $services->setService('config', []);
        $listener = new ServiceListener($services);
        $listener->addServiceManager(
            'UndefinedPluginManager',
            'undefined',
            TestAsset\UndefinedProviderInterface::class,
            'getUndefinedConfig'
        );

        $module = new TestAsset\ServiceProviderModule([]);

        $event          = new ModuleEvent();
        $configListener = new ConfigListener();
        $event->setConfigListener($configListener);

        $event->setModule($module);
        $listener->onLoadModule($event);

        try {
            $listener->onLoadModulesPost($event);
            self::assertFalse($services->has('UndefinedPluginManager'));
        } catch (\Exception $e) {
            $this->fail('Exception should not be raised when encountering unknown plugin manager services');
        }
    }
}
