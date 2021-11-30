<?php

declare(strict_types=1);

namespace Laminas\ModuleManager;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Traversable;

use function current;
use function get_class;
use function is_array;
use function is_object;
use function is_string;
use function key;
use function sprintf;

class ModuleManager implements ModuleManagerInterface
{
    /** Reference to Laminas\Mvc\MvcEvent::EVENT_BOOTSTRAP */
    public const EVENT_BOOTSTRAP = 'bootstrap';

    /** @var array An array of Module classes of loaded modules */
    protected $loadedModules = [];

    /** @var EventManagerInterface */
    protected $events;

    /** @var ModuleEvent */
    protected $event;

    /** @var int */
    protected $loadFinished;

    /** @var array|Traversable */
    protected $modules = [];

    /**
     * True if modules have already been loaded
     *
     * @var bool
     */
    protected $modulesAreLoaded = false;

    /** @param array|Traversable $modules */
    public function __construct($modules, ?EventManagerInterface $eventManager = null)
    {
        $this->setModules($modules);
        if ($eventManager instanceof EventManagerInterface) {
            $this->setEventManager($eventManager);
        }
    }

    /**
     * Handle the loadModules event
     */
    public function onLoadModules(): void
    {
        if (true === $this->modulesAreLoaded) {
            return;
        }

        foreach ($this->getModules() as $moduleName => $module) {
            if (is_object($module)) {
                if (! is_string($moduleName)) {
                    throw new Exception\RuntimeException(sprintf(
                        'Module (%s) must have a key identifier.',
                        get_class($module)
                    ));
                }
                $module = [$moduleName => $module];
            }

            $this->loadModule($module);
        }

        $this->modulesAreLoaded = true;
    }

    /**
     * Load the provided modules.
     *
     * {@inheritDoc}
     *
     * @triggers loadModules
     * @triggers loadModules.post
     */
    public function loadModules(): ModuleManager
    {
        if (true === $this->modulesAreLoaded) {
            return $this;
        }

        $events = $this->getEventManager();
        $event  = $this->getEvent();
        $event->setName(ModuleEvent::EVENT_LOAD_MODULES);

        $events->triggerEvent($event);

        /**
         * Having a dedicated .post event abstracts the complexity of priorities from the user.
         * Users can attach to the .post event and be sure that important
         * things like config merging are complete without having to worry if
         * they set a low enough priority.
         */
        $event->setName(ModuleEvent::EVENT_LOAD_MODULES_POST);
        $events->triggerEvent($event);

        return $this;
    }

    /**
     * Load a specific module by name.
     *
     * {@inheritDoc}
     *
     * @param  string|array               $module
     * @throws Exception\RuntimeException
     * @triggers loadModule.resolve
     * @triggers loadModule
     */
    public function loadModule($module)
    {
        $moduleName = $module;
        if (is_array($module)) {
            $moduleName = key($module);
            $module     = current($module);
        }

        if (isset($this->loadedModules[$moduleName])) {
            return $this->loadedModules[$moduleName];
        }

        /*
         * Keep track of nested module loading using the $loadFinished
         * property.
         *
         * Increment the value for each loadModule() call and then decrement
         * once the loading process is complete.
         *
         * To load a module, we clone the event if we are inside a nested
         * loadModule() call, and use the original event otherwise.
         */
        if (! isset($this->loadFinished)) {
            $this->loadFinished = 0;
        }

        $event = $this->loadFinished > 0 ? clone $this->getEvent() : $this->getEvent();
        $event->setModuleName($moduleName);

        $this->loadFinished++;

        if (! is_object($module)) {
            $module = $this->loadModuleByName($event);
        }
        $event->setModule($module);
        $event->setName(ModuleEvent::EVENT_LOAD_MODULE);

        $this->loadedModules[$moduleName] = $module;
        $this->getEventManager()->triggerEvent($event);

        $this->loadFinished--;

        return $module;
    }

    /**
     * Load a module with the name
     *
     * @return mixed                            module instance
     * @throws Exception\RuntimeException
     */
    protected function loadModuleByName(ModuleEvent $event)
    {
        $event->setName(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE);
        $result = $this->getEventManager()->triggerEventUntil(function ($r) {
            return is_object($r);
        }, $event);

        $module = $result->last();
        if (! is_object($module)) {
            throw new Exception\RuntimeException(sprintf(
                'Module (%s) could not be initialized.',
                $event->getModuleName()
            ));
        }

        return $module;
    }

    /** {@inheritDoc} */
    public function getLoadedModules($loadModules = false): array
    {
        if (true === $loadModules) {
            $this->loadModules();
        }

        return $this->loadedModules;
    }

    /**
     * Get an instance of a module class by the module name
     *
     * @return mixed
     */
    public function getModule(string $moduleName)
    {
        if (! isset($this->loadedModules[$moduleName])) {
            return;
        }
        return $this->loadedModules[$moduleName];
    }

    /**
     * Get the array of module names that this manager should load.
     *
     * @return Traversable|array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setModules($modules): ModuleManager
    {
        if (is_array($modules) || $modules instanceof Traversable) {
            $this->modules = $modules;
        } else {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Parameter to %s\'s %s method must be an array or implement the Traversable interface',
                    self::class,
                    __METHOD__
                )
            );
        }
        return $this;
    }

    public function getEvent(): ModuleEvent
    {
        if (! $this->event instanceof ModuleEvent) {
            $this->setEvent(new ModuleEvent());
        }
        return $this->event;
    }

    public function setEvent(ModuleEvent $event): ModuleManager
    {
        $event->setTarget($this);
        $this->event = $event;
        return $this;
    }

    /** {@inheritDoc} */
    public function setEventManager(EventManagerInterface $events): ModuleManager
    {
        $events->setIdentifiers([
            self::class,
            static::class,
            'module_manager',
        ]);
        $this->events = $events;
        $this->attachDefaultListeners($events);
        return $this;
    }

    /** {@inheritDoc} */
    public function getEventManager(): EventManagerInterface
    {
        if (! $this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Register the default event listeners
     */
    protected function attachDefaultListeners(EventManagerInterface $events): void
    {
        $events->attach(ModuleEvent::EVENT_LOAD_MODULES, [$this, 'onLoadModules']);
    }
}
