<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use ArrayObject;
use InvalidArgumentException;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\ModuleManager\Listener\AbstractListener;
use Laminas\ModuleManager\Listener\ConfigListener;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use LaminasTest\ModuleManager\SetUpCacheDirTrait;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function count;

#[CoversClass(ConfigListener::class)]
#[CoversClass(AbstractListener::class)]
final class ConfigListenerTest extends AbstractListenerTestCase
{
    use EventListenerIntrospectionTrait;
    use SetUpCacheDirTrait;

    /** @var ModuleManager */
    protected $moduleManager;

    #[Override]
    protected function setUp(): void
    {
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener(),
            1000
        );
    }

    public function testMultipleConfigsAreMerged(): void
    {
        $configListener = new ConfigListener();

        $moduleManager = $this->moduleManager;
        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->setModules(['SomeModule', 'ListenerTestModule']);
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig();
        self::assertSame(2, count($config));
        self::assertSame('test', $config['listener']);
        self::assertSame('thing', $config['some']);
        self::assertIsArray($config);
    }

    public function testCanCacheMergedConfig(): void
    {
        $options        = new ListenerOptions([
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
            new ModuleResolverListener(),
            1000
        );
        $configListener = new ConfigListener($options);
        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();
        $modules = $moduleManager->getLoadedModules();
        self::assertFalse($modules['ListenerTestModule']->getConfigCalled);
    }

    public function testBadConfigValueThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $configListener = new ConfigListener();

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['BadConfigModule', 'SomeModule']);
        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();
    }

    public function testBadGlobPathTrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $configListener = new ConfigListener();
        $configListener->addConfigGlobPath(['asd']);
    }

    public function testBadGlobPathArrayTrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $configListener = new ConfigListener();
        $configListener->addConfigGlobPaths('asd');
    }

    public function testBadStaticPathArrayTrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $configListener = new ConfigListener();
        $configListener->addConfigStaticPaths('asd');
    }

    public function testCanMergeConfigFromGlob(): void
    {
        $configListener = new ConfigListener();
        $configListener->addConfigGlobPath(__DIR__ . '/_files/good/*.php');

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $config = $configListener->getMergedConfig();
        self::assertSame($config, $configListener->getMergedConfig());
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['php2']);
        self::assertSame('loaded', $config['php3']);
    }

    public function testCanMergeConfigFromStaticPath(): void
    {
        $configListener = new ConfigListener();
        $configListener->addConfigStaticPath(__DIR__ . '/_files/good/config.php');
        $configListener->addConfigStaticPath(__DIR__ . '/_files/good/config2.php');
        $configListener->addConfigStaticPath(__DIR__ . '/_files/good/config3.php');

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $config = $configListener->getMergedConfig();
        self::assertSame($config, $configListener->getMergedConfig());
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['php2']);
        self::assertSame('loaded', $config['php3']);
    }

    public function testCanMergeConfigFromStaticPaths(): void
    {
        $configListener = new ConfigListener();
        $configListener->addConfigStaticPaths([
            __DIR__ . '/_files/good/config.php',
            __DIR__ . '/_files/good/config2.php',
            __DIR__ . '/_files/good/config3.php',
        ]);

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $config = $configListener->getMergedConfig();
        self::assertSame($config, $configListener->getMergedConfig());
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['php2']);
        self::assertSame('loaded', $config['php3']);
    }

    public function testNonPhpConfigFileThrowsInvalidArgumentException(): void
    {
        $configListener = new ConfigListener();
        $configListener->addConfigStaticPath(__DIR__ . '/_files/bad/config.badext');

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not a PHP file');
        $moduleManager->loadModules();
    }

    public function testCanCacheMergedConfigFromGlob(): void
    {
        $options        = new ListenerOptions([
            'cache_dir'            => $this->tmpdir,
            'config_cache_enabled' => true,
        ]);
        $configListener = new ConfigListener($options);
        $configListener->addConfigGlobPath(__DIR__ . '/_files/good/*.php');

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $configFromGlob = $configListener->getMergedConfig();

        // This time, don't add the glob path
        $configListener = new ConfigListener($options);
        $moduleManager  = new ModuleManager(['SomeModule']);
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener(),
            1000
        );

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();

        // Check if the values loaded from disk and from the cache are the same
        $configFromCache = $configListener->getMergedConfig();
        foreach (['php', 'php2', 'php3'] as $key) {
            self::assertArrayHasKey($key, $configFromGlob);
            self::assertSame($configFromGlob[$key], $configFromCache[$key]);
        }
    }

    public function testCanCacheMergedConfigFromStatic(): void
    {
        $options        = new ListenerOptions([
            'cache_dir'            => $this->tmpdir,
            'config_cache_enabled' => true,
        ]);
        $configListener = new ConfigListener($options);
        $configListener->addConfigStaticPaths([
            __DIR__ . '/_files/good/config.php',
            __DIR__ . '/_files/good/config2.php',
            __DIR__ . '/_files/good/config3.php',
        ]);

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();
        $configFromGlob = $configListener->getMergedConfig();

        // This time, don't add the glob path
        $configListener = new ConfigListener($options);
        $moduleManager  = new ModuleManager(['SomeModule']);
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,
            new ModuleResolverListener(),
            1000
        );

        $configListener->attach($moduleManager->getEventManager());

        $moduleManager->loadModules();

        // Check if the values loaded from disk and from the cache are the same
        $configFromCache = $configListener->getMergedConfig();
        foreach (['php', 'php2', 'php3'] as $key) {
            self::assertArrayHasKey($key, $configFromGlob);
            self::assertSame($configFromGlob[$key], $configFromCache[$key]);
        }
    }

    public function testCanMergeConfigFromArrayOfGlobs(): void
    {
        $configListener = new ConfigListener();
        $configListener->addConfigGlobPaths(new ArrayObject([
            __DIR__ . '/_files/good/config.php',
            __DIR__ . '/_files/good/config[23].php',
        ]));

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig();
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['php2']);
        self::assertSame('loaded', $config['php3']);
    }

    public function testCanMergeConfigFromArrayOfStatic(): void
    {
        $configListener = new ConfigListener();
        $configListener->addConfigStaticPaths(new ArrayObject([
            __DIR__ . '/_files/good/config.php',
            __DIR__ . '/_files/good/config2.php',
            __DIR__ . '/_files/good/config3.php',
        ]));

        $moduleManager = $this->moduleManager;
        $moduleManager->setModules(['SomeModule']);

        $configListener->attach($moduleManager->getEventManager());
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig();
        self::assertSame('loaded', $config['php']);
        self::assertSame('loaded', $config['php2']);
        self::assertSame('loaded', $config['php3']);
    }

    public function testMergesWithMergeAndReplaceBehavior(): void
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

        $mergedConfig = $configListener->getMergedConfig();
        self::assertSame(['foo', 'bar'], $mergedConfig['indexed']);
        self::assertSame('bar', $mergedConfig['keyed']);
    }

    public function testConfigListenerFunctionsAsAggregateListener(): void
    {
        $configListener = new ConfigListener();

        $moduleManager = $this->moduleManager;
        $events        = $moduleManager->getEventManager();
        self::assertEquals(2, count($this->getEventsFromEventManager($events)));

        $configListener->attach($events);
        self::assertEquals(4, count($this->getEventsFromEventManager($events)));

        $configListener->detach($events);
        self::assertEquals(2, count($this->getEventsFromEventManager($events)));
    }
}
