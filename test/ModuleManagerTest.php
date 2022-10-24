<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager;

use InvalidArgumentException;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\ModuleManager\Exception;
use Laminas\ModuleManager\Listener\DefaultListenerAggregate;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SomeModule\Module;
use stdClass;
use SubModule\Sub\Module as SubModule;

use function array_values;
use function count;
use function realpath;
use function var_export;

/**
 * @covers \Laminas\ModuleManager\ModuleManager
 */
class ModuleManagerTest extends TestCase
{
    use ResetAutoloadFunctionsTrait;
    use SetUpCacheDirTrait;

    /** @var DefaultListenerAggregate */
    protected $defaultListeners;

    protected function setUp(): void
    {
        $this->sharedEvents     = new SharedEventManager();
        $this->events           = new EventManager($this->sharedEvents);
        $this->defaultListeners = new DefaultListenerAggregate(
            new ListenerOptions([
                'module_paths' => [
                    realpath(__DIR__ . '/TestAsset'),
                ],
            ])
        );
    }

    public function testEventManagerIdentifiers(): void
    {
        $moduleManager = new ModuleManager([]);
        $identifiers   = $moduleManager->getEventManager()->getIdentifiers();
        $expected      = [ModuleManager::class, 'module_manager'];
        self::assertEquals($expected, array_values($identifiers));
    }

    public function testCanLoadSomeModule(): void
    {
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['SomeModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();
        $loadedModules = $moduleManager->getLoadedModules();
        self::assertInstanceOf('SomeModule\Module', $loadedModules['SomeModule']);
        $config = $configListener->getMergedConfig();
        self::assertSame($config->some, 'thing', var_export($config, true));
    }

    public function testCanLoadMultipleModules(): void
    {
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['BarModule', 'BazModule', 'SubModule\Sub'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();
        $loadedModules = $moduleManager->getLoadedModules();
        self::assertInstanceOf('BarModule\Module', $loadedModules['BarModule']);
        self::assertInstanceOf('BazModule\Module', $loadedModules['BazModule']);
        self::assertInstanceOf('SubModule\Sub\Module', $loadedModules['SubModule\Sub']);
        self::assertInstanceOf('BarModule\Module', $moduleManager->getModule('BarModule'));
        self::assertInstanceOf('BazModule\Module', $moduleManager->getModule('BazModule'));
        self::assertInstanceOf('SubModule\Sub\Module', $moduleManager->getModule('SubModule\Sub'));
        self::assertNull($moduleManager->getModule('NotLoaded'));
        $config = $configListener->getMergedConfig();
        self::assertSame('foo', $config->bar);
        self::assertSame('bar', $config->baz);
    }

    public function testModuleLoadingBehavior(): void
    {
        $moduleManager = new ModuleManager(['BarModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $modules = $moduleManager->getLoadedModules();
        self::assertSame(0, count($modules));
        $modules = $moduleManager->getLoadedModules(true);
        self::assertSame(1, count($modules));
        $moduleManager->loadModules(); // should not cause any problems
        $moduleManager->loadModule('BarModule'); // should not cause any problems
        $modules = $moduleManager->getLoadedModules(true); // BarModule already loaded so nothing happens
        self::assertSame(1, count($modules));
    }

    public function testConstructorThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ModuleManager('stringShouldBeArray', $this->events);
    }

    public function testNotFoundModuleThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $moduleManager = new ModuleManager(['NotFoundModule'], $this->events);
        $moduleManager->loadModules();
    }

    public function testCanLoadModuleDuringTheLoadModuleEvent(): void
    {
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['LoadOtherModule', 'BarModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig();
        self::assertTrue(isset($config['loaded']));
        self::assertSame('oh, yeah baby!', $config['loaded']);
    }

    /** @group 5651 */
    public function testLoadingModuleFromAnotherModuleDemonstratesAppropriateSideEffects(): void
    {
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['LoadOtherModule', 'BarModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig();
        self::assertTrue(isset($config['baz']));
        self::assertSame('bar', $config['baz']);
    }

    /**
     * @group 5651
     * @group 5948
     */
    public function testLoadingModuleFromAnotherModuleDoesNotInfiniteLoop(): void
    {
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['LoadBarModule', 'LoadFooModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig();

        self::assertTrue(isset($config['bar']));
        self::assertSame('bar', $config['bar']);

        self::assertTrue(isset($config['foo']));
        self::assertSame('bar', $config['foo']);
    }

    public function testModuleIsMarkedAsLoadedWhenLoadModuleEventIsTriggered(): void
    {
        $test          = new stdClass();
        $moduleManager = new ModuleManager(['BarModule'], $this->events);
        $events        = $this->events;
        $this->defaultListeners->attach($events);
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE, static function (ModuleEvent $e) use ($test): void {
            $test->modules = $e->getTarget()->getLoadedModules(false);
        });

        $moduleManager->loadModules();

        self::assertTrue(isset($test->modules));
        self::assertArrayHasKey('BarModule', $test->modules);
        self::assertInstanceOf('BarModule\Module', $test->modules['BarModule']);
    }

    public function testCanLoadSomeObjectModule(): void
    {
        require_once __DIR__ . '/TestAsset/SomeModule/Module.php';
        require_once __DIR__ . '/TestAsset/SubModule/Sub/Module.php';
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager([
            'SomeModule' => new Module(),
            'SubModule'  => new SubModule(),
        ], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();
        $loadedModules = $moduleManager->getLoadedModules();
        self::assertInstanceOf('SomeModule\Module', $loadedModules['SomeModule']);
        $config = $configListener->getMergedConfig();
        self::assertSame($config->some, 'thing');
    }

    public function testCanLoadMultipleModulesObjectWithString(): void
    {
        require_once __DIR__ . '/TestAsset/SomeModule/Module.php';
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['SomeModule' => new Module(), 'BarModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();
        $loadedModules = $moduleManager->getLoadedModules();
        self::assertInstanceOf('SomeModule\Module', $loadedModules['SomeModule']);
        $config = $configListener->getMergedConfig();
        self::assertSame($config->some, 'thing');
    }

    public function testCanNotLoadSomeObjectModuleWithoutIdentifier(): void
    {
        require_once __DIR__ . '/TestAsset/SomeModule/Module.php';
        $this->defaultListeners->getConfigListener();
        $moduleManager = new ModuleManager([new Module()], $this->events);
        $this->defaultListeners->attach($this->events);
        $this->expectException(Exception\RuntimeException::class);
        $moduleManager->loadModules();
    }

    public function testSettingEventInjectsModuleManagerAsTarget(): void
    {
        $moduleManager = new ModuleManager([]);
        $event         = new ModuleEvent();

        $moduleManager->setEvent($event);

        self::assertSame($event, $moduleManager->getEvent());
        self::assertSame($moduleManager, $event->getTarget());
    }

    public function testGetEventWillLazyLoadOneWithTargetSetToModuleManager(): void
    {
        $moduleManager = new ModuleManager([]);
        $event         = $moduleManager->getEvent();
        self::assertInstanceOf(ModuleEvent::class, $event);
        self::assertSame($moduleManager, $event->getTarget());
    }
}
