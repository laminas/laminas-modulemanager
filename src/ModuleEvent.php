<?php

declare(strict_types=1);

namespace Laminas\ModuleManager;

use Laminas\EventManager\Event;
use Laminas\ModuleManager\Listener\ConfigMergerInterface;

use function gettype;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Custom event for use with module manager
 * Composes Module objects
 */
class ModuleEvent extends Event
{
    /**
     * Module events triggered by eventmanager
     */
    public const EVENT_MERGE_CONFIG        = 'mergeConfig';
    public const EVENT_LOAD_MODULES        = 'loadModules';
    public const EVENT_LOAD_MODULE_RESOLVE = 'loadModule.resolve';
    public const EVENT_LOAD_MODULE         = 'loadModule';
    public const EVENT_LOAD_MODULES_POST   = 'loadModules.post';

    /** @var null|object */
    protected $module;

    /** @var string */
    protected $moduleName;

    /** @var ConfigMergerInterface */
    protected $configListener;

    /** @return string */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Set the name of a given module
     *
     * @param  string $moduleName
     * @throws Exception\InvalidArgumentException
     */
    public function setModuleName($moduleName): ModuleEvent
    {
        if (! is_string($moduleName)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s expects a string as an argument; %s provided',
                    __METHOD__,
                    gettype($moduleName)
                )
            );
        }
        // Performance tweak, don't add it as param.
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * Get module object
     *
     * @return null|object
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set module object to compose in this event
     *
     * @param  object $module
     * @throws Exception\InvalidArgumentException
     */
    public function setModule($module): ModuleEvent
    {
        if (! is_object($module)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s expects a module object as an argument; %s provided',
                    __METHOD__,
                    gettype($module)
                )
            );
        }
        // Performance tweak, don't add it as param.
        $this->module = $module;

        return $this;
    }

    public function getConfigListener(): ?ConfigMergerInterface
    {
        return $this->configListener;
    }

    /**
     * Set module object to compose in this event
     */
    public function setConfigListener(ConfigMergerInterface $configListener): ModuleEvent
    {
        $this->setParam('configListener', $configListener);
        $this->configListener = $configListener;

        return $this;
    }
}
