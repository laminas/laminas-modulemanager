<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use ArrayObject;
use InvalidArgumentException;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\Listener\ModuleLoaderListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit_Framework_TestCase as TestCase;

class ModuleLoaderListenerTest extends TestCase
{
    public function setUp()
    {
        $this->tmpdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laminas_module_cache_dir';
        @mkdir($this->tmpdir);

        $this->moduleManager = new ModuleManager(array());
        $this->moduleManager->getEventManager()->attach('loadModule.resolve', new ModuleResolverListener, 1000);
    }

    public function tearDown()
    {
        $file = glob($this->tmpdir . DIRECTORY_SEPARATOR . '*');
        @unlink($file[0]); // change this if there's ever > 1 file
        @rmdir($this->tmpdir);
    }

    public function testModuleLoaderListenerFunctionsAsAggregateListenerEnabledCache()
    {
        $options = new ListenerOptions(array(
            'cache_dir'                => $this->tmpdir,
            'module_map_cache_enabled' => true,
            'module_map_cache_key'     => 'foo',
        ));

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
        $options = new ListenerOptions(array(
            'cache_dir' => $this->tmpdir,
        ));

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
        $options = new ListenerOptions(array(
            'cache_dir'                => $this->tmpdir,
            'module_map_cache_key'     => 'foo',
            'module_map_cache_enabled' => true,
        ));

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
