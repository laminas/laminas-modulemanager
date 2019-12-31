<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\Listener\ModuleLoaderListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use LaminasTest\ModuleManager\SetUpCacheDirTrait;

/**
 * @covers Laminas\ModuleManager\Listener\AbstractListener
 * @covers Laminas\ModuleManager\Listener\ModuleLoaderListener
 */
class ModuleLoaderListenerTest extends AbstractListenerTestCase
{
    use EventListenerIntrospectionTrait;
    use SetUpCacheDirTrait;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    public function setUp()
    {
        $this->moduleManager = new ModuleManager([]);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener, 1000);
    }

    public function testModuleLoaderListenerFunctionsAsAggregateListenerEnabledCache()
    {
        $options = new ListenerOptions([
            'cache_dir'                => $this->tmpdir,
            'module_map_cache_enabled' => true,
            'module_map_cache_key'     => 'foo',
        ]);

        $moduleLoaderListener = new ModuleLoaderListener($options);

        $moduleManager = $this->moduleManager;
        $events        = $moduleManager->getEventManager();

        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES, $events));
        $this->assertCount(1, $listeners);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $events));
        $this->assertCount(0, $listeners);

        $moduleLoaderListener->attach($events);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES, $events));
        $this->assertCount(2, $listeners);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $events));
        $this->assertCount(1, $listeners);
    }

    public function testModuleLoaderListenerFunctionsAsAggregateListenerDisabledCache()
    {
        $options = new ListenerOptions([
            'cache_dir' => $this->tmpdir,
        ]);

        $moduleLoaderListener = new ModuleLoaderListener($options);

        $moduleManager = $this->moduleManager;
        $events        = $moduleManager->getEventManager();

        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES, $events));
        $this->assertCount(1, $listeners);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $events));
        $this->assertCount(0, $listeners);

        $moduleLoaderListener->attach($events);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES, $events));
        $this->assertCount(2, $listeners);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $events));
        $this->assertCount(0, $listeners);
    }

    public function testModuleLoaderListenerFunctionsAsAggregateListenerHasCache()
    {
        $options = new ListenerOptions([
            'cache_dir'                => $this->tmpdir,
            'module_map_cache_key'     => 'foo',
            'module_map_cache_enabled' => true,
        ]);

        file_put_contents($options->getModuleMapCacheFile(), '<' . '?php return array();');

        $moduleLoaderListener = new ModuleLoaderListener($options);

        $moduleManager = $this->moduleManager;
        $events        = $moduleManager->getEventManager();

        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES, $events));
        $this->assertCount(1, $listeners);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $events));
        $this->assertCount(0, $listeners);

        $moduleLoaderListener->attach($events);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES, $events));
        $this->assertCount(2, $listeners);
        $listeners     = iterator_to_array($this->getListenersForEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $events));
        $this->assertCount(0, $listeners);
    }
}
