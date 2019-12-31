<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\ModuleManager\Feature\LocatorRegisteredInterface;
use Laminas\ModuleManager\ModuleEvent;

/**
 * Locator registration listener
 *
 * @category   Laminas
 * @package    Laminas_ModuleManager
 * @subpackage Listener
 */
class LocatorRegistrationListener extends AbstractListener implements
    ListenerAggregateInterface
{
    /**
     * @var array
     */
    protected $modules = array();

    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * loadModule
     *
     * Check each loaded module to see if it implements LocatorRegistered. If it
     * does, we add it to an internal array for later.
     *
     * @param  ModuleEvent $e
     * @return void
     */
    public function onLoadModule(ModuleEvent $e)
    {
        if (!$e->getModule() instanceof LocatorRegisteredInterface) {
            return;
        }
        $this->modules[] = $e->getModule();
    }

    /**
     * loadModulesPost
     *
     * Once all the modules are loaded, loop
     *
     * @param  Event $e
     * @return void
     */
    public function onLoadModulesPost(Event $e)
    {
        $moduleManager = $e->getTarget();
        $events        = $moduleManager->getEventManager()->getSharedManager();

        // Shared instance for module manager
        $events->attach('Laminas\Mvc\Application', 'bootstrap', function ($e) use ($moduleManager) {
            $moduleClassName = get_class($moduleManager);
            $application     = $e->getApplication();
            $services        = $application->getServiceManager();
            if (!$services->has($moduleClassName)) {
                $services->setService($moduleClassName, $moduleManager);
            }
        }, 1000);

        if (0 === count($this->modules)) {
            return;
        }

        // Attach to the bootstrap event if there are modules we need to process
        $events->attach('Laminas\Mvc\Application', 'bootstrap', array($this, 'onBootstrap'), 1000);
    }

    /**
     * Bootstrap listener
     *
     * This is ran during the MVC bootstrap event because it requires access to
     * the DI container.
     *
     * @TODO: Check the application / locator / etc a bit better to make sure
     * the env looks how we're expecting it to?
     * @param Event $e
     * @return void
     */
    public function onBootstrap(Event $e)
    {
        $application = $e->getApplication();
        $services    = $application->getServiceManager();

        foreach ($this->modules as $module) {
            $moduleClassName = get_class($module);
            if (!$services->has($moduleClassName)) {
                $services->setService($moduleClassName, $module);
            }
        }
    }

    /**
     * Attach one or more listeners
     *
     * @param  EventManagerInterface $events
     * @return LocatorRegistrationListener
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, array($this, 'onLoadModule'));
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, array($this, 'onLoadModulesPost'), -1000);
        return $this;
    }

    /**
     * Detach all previously attached listeners
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $key => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$key]);
            }
        }
    }
}
