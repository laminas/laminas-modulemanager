<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\Loader\AutoloaderFactory;
use Laminas\Loader\ModuleAutoloader;
use Laminas\ModuleManager\Listener\LocatorRegistrationListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ModuleManager\TestAsset\MockApplication;
use PHPUnit_Framework_TestCase as TestCase;

require_once dirname(__DIR__) . '/TestAsset/ListenerTestModule/src/Foo/Bar.php';

class LocatorRegistrationListenerTest extends TestCase
{
    public $module;

    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Store original include_path
        $this->includePath = get_include_path();

        $autoloader = new ModuleAutoloader(array(
            dirname(__DIR__) . '/TestAsset',
        ));
        $autoloader->register();

        $this->sharedEvents = new SharedEventManager();

        $this->moduleManager = new ModuleManager(array('ListenerTestModule'));
        $this->moduleManager->getEventManager()->setSharedManager($this->sharedEvents);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener, 1000);

        $this->application = new MockApplication;
        $events            = new EventManager(array('Laminas\Mvc\Application', 'LaminasTest\Module\TestAsset\MockApplication', 'application'));
        $events->setSharedManager($this->sharedEvents);
        $this->application->setEventManager($events);

        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setService('ModuleManager', $this->moduleManager);
        $this->application->setServiceManager($this->serviceManager);
    }

    public function tearDown()
    {
        // Restore original autoloaders
        AutoloaderFactory::unregisterAutoloaders();
        $loaders = spl_autoload_functions();
        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                spl_autoload_unregister($loader);
            }
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Restore original include_path
        set_include_path($this->includePath);
    }

    public function testModuleClassIsRegisteredWithDiAndInjectedWithSharedInstances()
    {
        $locator         = $this->serviceManager;
        $locator->setFactory('Foo\Bar', function ($s) {
            $module   = $s->get('ListenerTestModule\Module');
            $manager  = $s->get('Laminas\ModuleManager\ModuleManager');
            $instance = new \Foo\Bar($module, $manager);
            return $instance;
        });

        $locatorRegistrationListener = new LocatorRegistrationListener;
        $this->moduleManager->getEventManager()->attachAggregate($locatorRegistrationListener);
        $test = $this;
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, function ($e) use ($test) {
            $test->module = $e->getModule();
        }, -1000);
        $this->moduleManager->loadModules();

        $this->application->bootstrap();
        $sharedInstance1 = $locator->get('ListenerTestModule\Module');
        $sharedInstance2 = $locator->get('Laminas\ModuleManager\ModuleManager');

        $this->assertInstanceOf('ListenerTestModule\Module', $sharedInstance1);
        $foo     = false;
        $message = '';
        try {
            $foo = $locator->get('Foo\Bar');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            while ($e = $e->getPrevious()) {
                $message .= "\n" . $e->getMessage();
            }
        }
        if (!$foo) {
            $this->fail($message);
        }
        $this->assertSame($this->module, $foo->module);

        $this->assertInstanceOf('Laminas\ModuleManager\ModuleManager', $sharedInstance2);
        $this->assertSame($this->moduleManager, $locator->get('Foo\Bar')->moduleManager);
    }

    public function testNoDuplicateServicesAreDefinedForModuleManager()
    {
        $locatorRegistrationListener = new LocatorRegistrationListener;
        $this->moduleManager->getEventManager()->attachAggregate($locatorRegistrationListener);

        $this->moduleManager->loadModules();
        $this->application->bootstrap();
        $registeredServices = $this->application->getServiceManager()->getRegisteredServices();

        $aliases = $registeredServices['aliases'];
        $instances = $registeredServices['instances'];

        $this->assertContains('laminasmodulemanagermodulemanager', $aliases);
        $this->assertFalse(in_array('modulemanager', $aliases));

        $this->assertContains('modulemanager', $instances);
        $this->assertFalse(in_array('laminasmodulemanagermodulemanager', $instances));
    }
}
