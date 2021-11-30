<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

use Laminas\Config\Config;
use Laminas\Config\Factory as ConfigFactory;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\Glob;
use Traversable;

use function file_exists;
use function gettype;
use function is_array;
use function is_callable;
use function is_string;
use function sprintf;

class ConfigListener extends AbstractListener implements
    ConfigMergerInterface,
    ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public const STATIC_PATH = 'static_path';
    public const GLOB_PATH   = 'glob_path';

    /** @var array */
    protected $configs = [];

    /** @var array */
    protected $mergedConfig = [];

    /** @var Config|null */
    protected $mergedConfigObject;

    /** @var bool */
    protected $skipConfig = false;

    /** @var array */
    protected $paths = [];

    public function __construct(?ListenerOptions $options = null)
    {
        parent::__construct($options);
        if ($this->hasCachedConfig()) {
            $this->skipConfig = true;
            $this->setMergedConfig($this->getCachedConfig());
        } else {
            $this->addConfigGlobPaths($this->getOptions()->getConfigGlobPaths());
            $this->addConfigStaticPaths($this->getOptions()->getConfigStaticPaths());
        }
    }

    /** {@inheritDoc} */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, [$this, 'onloadModulesPre'], 1000);

        if ($this->skipConfig) {
            // We already have the config from cache, no need to collect or merge.
            return;
        }

        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, [$this, 'onLoadModule']);
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, [$this, 'onLoadModules'], -1000);
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onMergeConfig'], 1000);
    }

    /**
     * Pass self to the ModuleEvent object early so everyone has access.
     */
    public function onloadModulesPre(ModuleEvent $e): ConfigListener
    {
        $e->setConfigListener($this);

        return $this;
    }

    /**
     * Merge the config for each module
     */
    public function onLoadModule(ModuleEvent $e): ConfigListener
    {
        $module = $e->getModule();

        if (
            ! $module instanceof ConfigProviderInterface
            && ! is_callable([$module, 'getConfig'])
        ) {
            return $this;
        }

        $config = $module->getConfig();
        $this->addConfig($e->getModuleName(), $config);

        return $this;
    }

    /**
     * Merge all config files matched by the given glob()s
     *
     * This is only attached if config is not cached.
     */
    public function onMergeConfig(ModuleEvent $e): ConfigListener
    {
        // Load the config files
        foreach ($this->paths as $path) {
            $this->addConfigByPath($path['path'], $path['type']);
        }

        // Merge all of the collected configs
        $this->mergedConfig = $this->getOptions()->getExtraConfig() ?: [];
        foreach ($this->configs as $config) {
            $this->mergedConfig = ArrayUtils::merge($this->mergedConfig, $config);
        }

        return $this;
    }

    /**
     * Optionally cache merged config
     *
     * This is only attached if config is not cached.
     */
    public function onLoadModules(ModuleEvent $e): ConfigListener
    {
        // Trigger MERGE_CONFIG event. This is a hook to allow the merged application config to be
        // modified before it is cached (In particular, allows the removal of config keys)
        $originalEventName = $e->getName();
        $e->setName(ModuleEvent::EVENT_MERGE_CONFIG);
        $e->getTarget()->getEventManager()->triggerEvent($e);

        // Reset event name
        $e->setName($originalEventName);

        // If enabled, update the config cache
        if (
            $this->getOptions()->getConfigCacheEnabled()
            && false === $this->skipConfig
        ) {
            $configFile = $this->getOptions()->getConfigCacheFile();
            $this->writeArrayToFile($configFile, $this->getMergedConfig(false));
        }

        return $this;
    }

    /**
     * @param  bool $returnConfigAsObject
     * @return mixed
     */
    public function getMergedConfig($returnConfigAsObject = true)
    {
        if ($returnConfigAsObject === true) {
            if ($this->mergedConfigObject === null) {
                $this->mergedConfigObject = new Config($this->mergedConfig);
            }
            return $this->mergedConfigObject;
        }

        return $this->mergedConfig;
    }

    /** @param array $config */
    public function setMergedConfig(array $config): ConfigListener
    {
        $this->mergedConfig       = $config;
        $this->mergedConfigObject = null;
        return $this;
    }

    /**
     * Add an array of glob paths of config files to merge after loading modules
     *
     * @param array|Traversable $globPaths
     */
    public function addConfigGlobPaths($globPaths): ConfigListener
    {
        $this->addConfigPaths($globPaths, self::GLOB_PATH);
        return $this;
    }

    /**
     * Add a glob path of config files to merge after loading modules
     *
     * @param string $globPath
     */
    public function addConfigGlobPath($globPath): ConfigListener
    {
        $this->addConfigPath($globPath, self::GLOB_PATH);
        return $this;
    }

    /**
     * Add an array of static paths of config files to merge after loading modules
     *
     * @param array|Traversable $staticPaths
     */
    public function addConfigStaticPaths($staticPaths): ConfigListener
    {
        $this->addConfigPaths($staticPaths, self::STATIC_PATH);
        return $this;
    }

    /**
     * Add a static path of config files to merge after loading modules
     */
    public function addConfigStaticPath(string $staticPath): ConfigListener
    {
        $this->addConfigPath($staticPath, self::STATIC_PATH);
        return $this;
    }

    /**
     * Add an array of paths of config files to merge after loading modules
     *
     * @param  Traversable|array $paths
     * @throws Exception\InvalidArgumentException
     */
    protected function addConfigPaths($paths, string $type): void
    {
        if ($paths instanceof Traversable) {
            $paths = ArrayUtils::iteratorToArray($paths);
        }

        if (! is_array($paths)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Argument passed to %s::%s() must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Laminas\Config\Config. %s given.',
                    self::class,
                    __METHOD__,
                    gettype($paths)
                )
            );
        }

        foreach ($paths as $path) {
            $this->addConfigPath((string) $path, $type);
        }
    }

    /**
     * Add a path of config files to load and merge after loading modules
     *
     * @param  string $path
     * @throws Exception\InvalidArgumentException
     */
    protected function addConfigPath($path, string $type): ConfigListener
    {
        if (! is_string($path)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Parameter to %s::%s() must be a string; %s given.',
                    self::class,
                    __METHOD__,
                    gettype($path)
                )
            );
        }
        $this->paths[] = ['type' => $type, 'path' => $path];
        return $this;
    }

    /**
     * @param array|Traversable $config
     * @throws Exception\InvalidArgumentException
     */
    protected function addConfig(string $key, $config): ConfigListener
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (! is_array($config)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Config being merged must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Laminas\Config\Config. %s given.',
                    gettype($config)
                )
            );
        }

        $this->configs[$key] = $config;

        return $this;
    }

    /**
     * Given a path (glob or static), fetch the config and add it to the array
     * of configs to merge.
     */
    protected function addConfigByPath(string $path, string $type): ConfigListener
    {
        switch ($type) {
            case self::STATIC_PATH:
                $this->addConfig($path, ConfigFactory::fromFile($path));
                break;

            case self::GLOB_PATH:
                // We want to keep track of where each value came from so we don't
                // use ConfigFactory::fromFiles() since it does merging internally.
                foreach (Glob::glob($path, Glob::GLOB_BRACE) as $file) {
                    $this->addConfig((string) $file, ConfigFactory::fromFile((string) $file));
                }
                break;
        }

        return $this;
    }

    protected function hasCachedConfig(): bool
    {
        if (
            ($this->getOptions()->getConfigCacheEnabled())
            && (file_exists($this->getOptions()->getConfigCacheFile()))
        ) {
            return true;
        }
        return false;
    }

    /** @return mixed */
    protected function getCachedConfig()
    {
        return include $this->getOptions()->getConfigCacheFile();
    }
}
