<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

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
use stdClass;

/**
 * @covers \Laminas\ModuleManager\ModuleManager
 */
class ModuleManagerTest extends TestCase
{
    use ResetAutoloadFunctionsTrait;
    use SetUpCacheDirTrait;

    /**
     * @var DefaultListenerAggregate
     */
    protected $defaultListeners;

    protected function setUp() : void
    {
        $this->sharedEvents = new SharedEventManager;
        $this->events       = new EventManager($this->sharedEvents);
        $this->defaultListeners = new DefaultListenerAggregate(
            new ListenerOptions([
                'module_paths' => [
                    realpath(__DIR__ . '/TestAsset'),
                ],
            ])
        );
    }

    public function testEventManagerIdentifiers()
    {
        $moduleManager = new ModuleManager([]);
        $identifiers = $moduleManager->getEventManager()->getIdentifiers();
        $expected    = ['Laminas\ModuleManager\ModuleManager', 'module_manager'];
        self::assertEquals($expected, array_values($identifiers));
    }

    public function testCanLoadSomeModule()
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

    public function testCanLoadMultipleModules()
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

    public function testModuleLoadingBehavior()
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

    public function testConstructorThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $moduleManager = new ModuleManager('stringShouldBeArray', $this->events);
    }

    public function testNotFoundModuleThrowsRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $moduleManager = new ModuleManager(['NotFoundModule'], $this->events);
        $moduleManager->loadModules();
    }

    public function testCanLoadModuleDuringTheLoadModuleEvent()
    {
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['LoadOtherModule', 'BarModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();

        $config = $configListener->getMergedConfig();
        self::assertTrue(isset($config['loaded']));
        self::assertSame('oh, yeah baby!', $config['loaded']);
    }

    /**
     * @group 5651
     */
    public function testLoadingModuleFromAnotherModuleDemonstratesAppropriateSideEffects()
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
    public function testLoadingModuleFromAnotherModuleDoesNotInfiniteLoop()
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

    public function testModuleIsMarkedAsLoadedWhenLoadModuleEventIsTriggered()
    {
        $test          = new stdClass;
        $moduleManager = new ModuleManager(['BarModule'], $this->events);
        $events        = $this->events;
        $this->defaultListeners->attach($events);
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE, function (ModuleEvent $e) use ($test) {
            $test->modules = $e->getTarget()->getLoadedModules(false);
        });

        $moduleManager->loadModules();

        self::assertTrue(isset($test->modules));
        self::assertArrayHasKey('BarModule', $test->modules);
        self::assertInstanceOf('BarModule\Module', $test->modules['BarModule']);
    }

    public function testCanLoadSomeObjectModule()
    {
        require_once __DIR__ . '/TestAsset/SomeModule/Module.php';
        require_once __DIR__ . '/TestAsset/SubModule/Sub/Module.php';
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager([
            'SomeModule' => new \SomeModule\Module(),
            'SubModule' => new \SubModule\Sub\Module(),
        ], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();
        $loadedModules = $moduleManager->getLoadedModules();
        self::assertInstanceOf('SomeModule\Module', $loadedModules['SomeModule']);
        $config = $configListener->getMergedConfig();
        self::assertSame($config->some, 'thing');
    }

    public function testCanLoadMultipleModulesObjectWithString()
    {
        require_once __DIR__ . '/TestAsset/SomeModule/Module.php';
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager(['SomeModule' => new \SomeModule\Module(), 'BarModule'], $this->events);
        $this->defaultListeners->attach($this->events);
        $moduleManager->loadModules();
        $loadedModules = $moduleManager->getLoadedModules();
        self::assertInstanceOf('SomeModule\Module', $loadedModules['SomeModule']);
        $config = $configListener->getMergedConfig();
        self::assertSame($config->some, 'thing');
    }

    public function testCanNotLoadSomeObjectModuleWithoutIdentifier()
    {
        require_once __DIR__ . '/TestAsset/SomeModule/Module.php';
        $configListener = $this->defaultListeners->getConfigListener();
        $moduleManager  = new ModuleManager([new \SomeModule\Module()], $this->events);
        $this->defaultListeners->attach($this->events);
        $this->expectException(Exception\RuntimeException::class);
        $moduleManager->loadModules();
    }

    public function testSettingEventInjectsModuleManagerAsTarget()
    {
        $moduleManager = new ModuleManager([]);
        $event = new ModuleEvent();

        $moduleManager->setEvent($event);

        self::assertSame($event, $moduleManager->getEvent());
        self::assertSame($moduleManager, $event->getTarget());
    }

    public function testGetEventWillLazyLoadOneWithTargetSetToModuleManager()
    {
        $moduleManager = new ModuleManager([]);
        $event = $moduleManager->getEvent();
        self::assertInstanceOf(ModuleEvent::class, $event);
        self::assertSame($moduleManager, $event->getTarget());
    }
}
