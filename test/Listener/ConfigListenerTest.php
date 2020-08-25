<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use ArrayObject;
use InvalidArgumentException;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\ModuleManager\Listener\ConfigListener;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use LaminasTest\ModuleManager\SetUpCacheDirTrait;

use function count;
use function spl_object_hash;

/**
 * @covers \Laminas\ModuleManager\Listener\AbstractListener
 * @covers \Laminas\ModuleManager\Listener\ConfigListener
 */
class ConfigListenerTest extends AbstractListenerTestCase
{
    use EventListenerIntrospectionTrait;
    use SetUpCacheDirTrait;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    protected function setUp() : void
    {
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener,
            1000
        );
    }

    public function testMultipleConfigsAreMerged()
    {
        $configListener = new ConfigListener;

        $moduleManager = $this->moduleManager;
        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->setModules(['SomeModule', 'ListenerTestModule']);
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig(false);
        self::assertSame(2, count($config));
        self::assertSame('test', $config['listener']);
        self::assertSame('thing', $config['some']);
        $configObject = $configListener->getMergedConfig();
        self::assertInstanceOf('Laminas\Config\Config', $configObject);
    }

    public function testCanCacheMergedConfig()
    {
        $options = new ListenerOptions([
            'cache_dir'            => $this->tmpdir,
            'config_cache_enabled' => true,
        ]);
        $configListener = new ConfigListener($options);

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule', 'ListenerTestModule']);
        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules(); // This should cache the config

        $modules = $moduleManager->getLoadedModules();
        self::assertTrue($modules['ListenerTestModule']->getConfigCalled);

        // Now we check to make sure it uses the config and doesn't hit
        // the module objects getConfig() method(s)
        $moduleManager = new ModuleManager(['SomeModule', 'ListenerTestModule']);
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener,
            1000
        );
        $configListener = new ConfigListener($options);
        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        self::assertFalse($modules['ListenerTestModule']->getConfigCalled);
    }

    public function testBadConfigValueThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $configListener = new ConfigListener;

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['BadConfigModule', 'SomeModule']);
        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();
    }

    public function testBadGlobPathTrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $configListener = new ConfigListener;
        $configListener->addConfigGlobPath(['asd']);
    }

    public function testBadGlobPathArrayTrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $configListener = new ConfigListener;
        $configListener->addConfigGlobPaths('asd');
    }

    public function testBadStaticPathArrayTrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $configListener = new ConfigListener;
        $configListener->addConfigStaticPaths('asd');
    }

    public function testCanMergeConfigFromGlob()
    {
        $configListener = new ConfigListener;
        $configListener->addConfigGlobPath(__DIR__ . '/_files/good/*.{ini,php,xml}');

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $configObjectCheck = $configListener->getMergedConfig();

        // Test as object
        $configObject = $configListener->getMergedConfig();
        self::assertSame(spl_object_hash($configObjectCheck), spl_object_hash($configObject));
        self::assertSame('loaded', $configObject->ini);
        self::assertSame('loaded', $configObject->php);
        self::assertSame('loaded', $configObject->xml);
        // Test as array
        $config = $configListener->getMergedConfig(false);
        self::assertSame('loaded', $config['ini']);
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['xml']);
    }

    public function testCanMergeConfigFromStaticPath()
    {
        $configListener = new ConfigListener;
        $configListener->addConfigStaticPath(__DIR__ . '/_files/good/config.ini');
        $configListener->addConfigStaticPath(__DIR__ . '/_files/good/config.php');
        $configListener->addConfigStaticPath(__DIR__ . '/_files/good/config.xml');

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $configObjectCheck = $configListener->getMergedConfig();

        // Test as object
        $configObject = $configListener->getMergedConfig();
        self::assertSame(spl_object_hash($configObjectCheck), spl_object_hash($configObject));
        self::assertSame('loaded', $configObject->ini);
        self::assertSame('loaded', $configObject->php);
        self::assertSame('loaded', $configObject->xml);
        // Test as array
        $config = $configListener->getMergedConfig(false);
        self::assertSame('loaded', $config['ini']);
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['xml']);
    }

    public function testCanMergeConfigFromStaticPaths()
    {
        $configListener = new ConfigListener;
        $configListener->addConfigStaticPaths([
            __DIR__ . '/_files/good/config.ini',
            __DIR__ . '/_files/good/config.php',
            __DIR__ . '/_files/good/config.xml'
        ]);

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $configObjectCheck = $configListener->getMergedConfig();

        // Test as object
        $configObject = $configListener->getMergedConfig();
        self::assertSame(spl_object_hash($configObjectCheck), spl_object_hash($configObject));
        self::assertSame('loaded', $configObject->ini);
        self::assertSame('loaded', $configObject->php);
        self::assertSame('loaded', $configObject->xml);
        // Test as array
        $config = $configListener->getMergedConfig(false);
        self::assertSame('loaded', $config['ini']);
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['xml']);
    }

    public function testCanCacheMergedConfigFromGlob()
    {
        $options = new ListenerOptions([
            'cache_dir'            => $this->tmpdir,
            'config_cache_enabled' => true,
        ]);
        $configListener = new ConfigListener($options);
        $configListener->addConfigGlobPath(__DIR__ . '/_files/good/*.{ini,php,xml}');

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $configObjectFromGlob = $configListener->getMergedConfig();

        // This time, don't add the glob path
        $configListener = new ConfigListener($options);
        $moduleManager = new ModuleManager(['SomeModule']);
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener,
            1000
        );

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();

        // Check if values from glob object and cache object are the same
        $configObjectFromCache = $configListener->getMergedConfig();
        self::assertNotNull($configObjectFromGlob->ini);
        self::assertSame($configObjectFromGlob->ini, $configObjectFromCache->ini);
        self::assertNotNull($configObjectFromGlob->php);
        self::assertSame($configObjectFromGlob->php, $configObjectFromCache->php);
        self::assertNotNull($configObjectFromGlob->xml);
        self::assertSame($configObjectFromGlob->xml, $configObjectFromCache->xml);
    }

    public function testCanCacheMergedConfigFromStatic()
    {
        $options = new ListenerOptions([
            'cache_dir'            => $this->tmpdir,
            'config_cache_enabled' => true,
        ]);
        $configListener = new ConfigListener($options);
        $configListener->addConfigStaticPaths([
            __DIR__ . '/_files/good/config.ini',
            __DIR__ . '/_files/good/config.php',
            __DIR__ . '/_files/good/config.xml'
        ]);

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $configObjectFromGlob = $configListener->getMergedConfig();

        // This time, don't add the glob path
        $configListener = new ConfigListener($options);
        $moduleManager = new ModuleManager(['SomeModule']);
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener,
            1000
        );

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();

        // Check if values from glob object and cache object are the same
        $configObjectFromCache = $configListener->getMergedConfig();
        self::assertNotNull($configObjectFromGlob->ini);
        self::assertSame($configObjectFromGlob->ini, $configObjectFromCache->ini);
        self::assertNotNull($configObjectFromGlob->php);
        self::assertSame($configObjectFromGlob->php, $configObjectFromCache->php);
        self::assertNotNull($configObjectFromGlob->xml);
        self::assertSame($configObjectFromGlob->xml, $configObjectFromCache->xml);
    }

    public function testCanMergeConfigFromArrayOfGlobs()
    {
        $configListener = new ConfigListener;
        $configListener->addConfigGlobPaths(new ArrayObject([
            __DIR__ . '/_files/good/*.ini',
            __DIR__ . '/_files/good/*.php',
            __DIR__ . '/_files/good/*.xml',
        ]));

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();

        // Test as object
        $configObject = $configListener->getMergedConfig();
        self::assertSame('loaded', $configObject->ini);
        self::assertSame('loaded', $configObject->php);
        self::assertSame('loaded', $configObject->xml);
    }

    public function testCanMergeConfigFromArrayOfStatic()
    {
        $configListener = new ConfigListener;
        $configListener->addConfigStaticPaths(new ArrayObject([
            __DIR__ . '/_files/good/config.ini',
            __DIR__ . '/_files/good/config.php',
            __DIR__ . '/_files/good/config.xml',
        ]));

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();

        // Test as object
        $configObject = $configListener->getMergedConfig();
        self::assertSame('loaded', $configObject->ini);
        self::assertSame('loaded', $configObject->php);
        self::assertSame('loaded', $configObject->xml);
    }

    public function testMergesWithMergeAndReplaceBehavior()
    {
        $configListener = new ConfigListener();

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->addConfigStaticPaths([
            __DIR__ . '/_files/good/merge1.php',
            __DIR__ . '/_files/good/merge2.php',
        ]);

        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();

        $mergedConfig = $configListener->getMergedConfig(false);
        self::assertSame(['foo', 'bar'], $mergedConfig['indexed']);
        self::assertSame('bar', $mergedConfig['keyed']);
    }

    public function testConfigListenerFunctionsAsAggregateListener()
    {
        $configListener = new ConfigListener;

        $moduleManager = $this->moduleManager;
        $events        = $moduleManager->getEventManager();
        self::assertEquals(2, count($this->getEventsFromEventManager($events)));

        $configListener->attach($events);
        self::assertEquals(4, count($this->getEventsFromEventManager($events)));

        $configListener->detach($events);
        self::assertEquals(2, count($this->getEventsFromEventManager($events)));
    }
}
