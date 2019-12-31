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
use Laminas\ModuleManager\ModuleManager;

/**
 * Locator registration listener
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
    protected $callbacks = array();

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
     * loadModules
     *
     * Once all the modules are loaded, loop
     *
     * @param  Event $e
     * @return void
     */
    public function onLoadModules(Event $e)
    {
        $moduleManager = $e->getTarget();
        $events        = $moduleManager->getEventManager()->getSharedManager();

        if (!$events) {
            return;
        }

        // Shared instance for module manager
        $events->attach('Laminas\Mvc\Application', ModuleManager::EVENT_BOOTSTRAP, function ($e) use ($moduleManager) {
            $moduleClassName = get_class($moduleManager);
            $moduleClassNameArray = explode('\\', $moduleClassName);
            $moduleClassNameAlias = end($moduleClassNameArray);
            $application     = $e->getApplication();
            $services        = $application->getServiceManager();
            if (!$services->has($moduleClassName)) {
                $services->setAlias($moduleClassName, $moduleClassNameAlias);
            }
        }, 1000);

        if (0 === count($this->modules)) {
            return;
        }

        // Attach to the bootstrap event if there are modules we need to process
        $events->attach('Laminas\Mvc\Application', ModuleManager::EVENT_BOOTSTRAP, array($this, 'onBootstrap'), 1000);
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
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->callbacks[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, array($this, 'onLoadModule'));
        $this->callbacks[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, array($this, 'onLoadModules'), -1000);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->callbacks as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->callbacks[$index]);
            }
        }
    }
}
