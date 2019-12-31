<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

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
        $this->assertEquals(1, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES)));
        $this->assertEquals(0, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES_POST)));

        $moduleLoaderListener->attach($moduleManager->getEventManager());
        $this->assertEquals(2, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES)));
        $this->assertEquals(1, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES_POST)));
    }

    public function testModuleLoaderListenerFunctionsAsAggregateListenerDisabledCache()
    {
        $options = new ListenerOptions([
            'cache_dir' => $this->tmpdir,
        ]);

        $moduleLoaderListener = new ModuleLoaderListener($options);

        $moduleManager = $this->moduleManager;
        $this->assertEquals(1, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES)));
        $this->assertEquals(0, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES_POST)));

        $moduleLoaderListener->attach($moduleManager->getEventManager());
        $this->assertEquals(2, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES)));
        $this->assertEquals(0, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES_POST)));
    }

    public function testModuleLoaderListenerFunctionsAsAggregateListenerHasCache()
    {
        $options = new ListenerOptions([
            'cache_dir'                => $this->tmpdir,
            'module_map_cache_key'     => 'foo',
            'module_map_cache_enabled' => true,
        ]);

        file_put_contents($options->getModuleMapCacheFile(), '<?php return array();');

        $moduleLoaderListener = new ModuleLoaderListener($options);

        $moduleManager = $this->moduleManager;
        $this->assertEquals(1, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES)));
        $this->assertEquals(0, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES_POST)));

        $moduleLoaderListener->attach($moduleManager->getEventManager());
        $this->assertEquals(2, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES)));
        $this->assertEquals(0, count($moduleManager->getEventManager()->getListeners(ModuleEvent::EVENT_LOAD_MODULES_POST)));
    }
}
