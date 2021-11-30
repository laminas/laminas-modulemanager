<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

use Laminas\Stdlib\AbstractOptions;
use Traversable;

use function func_get_args;
use function gettype;
use function is_array;
use function rtrim;
use function sprintf;

class ListenerOptions extends AbstractOptions
{
    /** @var array|Traversable */
    protected $modulePaths = [];

    /** @var array|Traversable */
    protected $configGlobPaths = [];

    /** @var array|Traversable */
    protected $configStaticPaths = [];

    /** @var array|Traversable */
    protected $extraConfig = [];

    /** @var bool */
    protected $configCacheEnabled = false;

    /** @var string */
    protected $configCacheKey;

    /** @var string|null */
    protected $cacheDir;

    /** @var bool */
    protected $checkDependencies = true;

    /** @var bool */
    protected $moduleMapCacheEnabled = false;

    /** @var string */
    protected $moduleMapCacheKey;

    /** @var bool */
    protected $useLaminasLoader = true;

    /**
     * Get an array of paths where modules reside
     *
     * @return array|Traversable
     */
    public function getModulePaths()
    {
        return $this->modulePaths;
    }

    /**
     * Set an array of paths where modules reside
     *
     * @param  array|Traversable $modulePaths
     * @throws Exception\InvalidArgumentException
     */
    public function setModulePaths($modulePaths): ListenerOptions
    {
        if (! is_array($modulePaths) && ! $modulePaths instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Argument passed to %s::%s() must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Laminas\Config\Config. %s given.',
                    self::class,
                    __METHOD__,
                    gettype($modulePaths)
                )
            );
        }

        $this->modulePaths = $modulePaths;
        return $this;
    }

    /**
     * Get the glob patterns to load additional config files
     *
     * @return array|Traversable
     */
    public function getConfigGlobPaths()
    {
        return $this->configGlobPaths;
    }

    /**
     * Get the static paths to load additional config files
     *
     * @return array|Traversable
     */
    public function getConfigStaticPaths()
    {
        return $this->configStaticPaths;
    }

    /**
     * Set the glob patterns to use for loading additional config files
     *
     * @param array|Traversable $configGlobPaths
     * @throws Exception\InvalidArgumentException
     */
    public function setConfigGlobPaths($configGlobPaths): ListenerOptions
    {
        if (! is_array($configGlobPaths) && ! $configGlobPaths instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Argument passed to %s::%s() must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Laminas\Config\Config. %s given.',
                    self::class,
                    __METHOD__,
                    gettype($configGlobPaths)
                )
            );
        }

        $this->configGlobPaths = $configGlobPaths;
        return $this;
    }

    /**
     * Set the static paths to use for loading additional config files
     *
     * @param array|Traversable $configStaticPaths
     * @throws Exception\InvalidArgumentException
     */
    public function setConfigStaticPaths($configStaticPaths): ListenerOptions
    {
        if (! is_array($configStaticPaths) && ! $configStaticPaths instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Argument passed to %s::%s() must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Laminas\Config\Config. %s given.',
                    self::class,
                    __METHOD__,
                    gettype($configStaticPaths)
                )
            );
        }

        $this->configStaticPaths = $configStaticPaths;
        return $this;
    }

    /**
     * Get any extra config to merge in.
     *
     * @return array|Traversable
     */
    public function getExtraConfig()
    {
        return $this->extraConfig;
    }

    /**
     * Add some extra config array to the main config. This is mainly useful
     * for unit testing purposes.
     *
     * @param array|Traversable $extraConfig
     * @throws Exception\InvalidArgumentException
     * @return ListenerOptions Provides fluent interface
     */
    public function setExtraConfig($extraConfig): ListenerOptions
    {
        if (! is_array($extraConfig) && ! $extraConfig instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Argument passed to %s::%s() must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Laminas\Config\Config. %s given.',
                    self::class,
                    __METHOD__,
                    gettype($extraConfig)
                )
            );
        }

        $this->extraConfig = $extraConfig;
        return $this;
    }

    /**
     * Check if the config cache is enabled
     */
    public function getConfigCacheEnabled(): bool
    {
        return $this->configCacheEnabled;
    }

    /**
     * Set if the config cache should be enabled or not
     *
     * @param bool $enabled
     */
    public function setConfigCacheEnabled($enabled): ListenerOptions
    {
        $this->configCacheEnabled = (bool) $enabled;
        return $this;
    }

    /**
     * Get key used to create the cache file name
     */
    public function getConfigCacheKey(): string
    {
        return (string) $this->configCacheKey;
    }

    /**
     * Set key used to create the cache file name
     *
     * @param  string $configCacheKey the value to be set
     */
    public function setConfigCacheKey($configCacheKey): ListenerOptions
    {
        $this->configCacheKey = $configCacheKey;
        return $this;
    }

    /**
     * Get the path to the config cache
     *
     * Should this be an option, or should the dir option include the
     * filename, or should it simply remain hard-coded? Thoughts?
     */
    public function getConfigCacheFile(): string
    {
        if ($this->getCacheDir() && $this->getConfigCacheKey()) {
            return $this->getCacheDir() . '/module-config-cache.' . $this->getConfigCacheKey() . '.php';
        }

        return $this->getCacheDir() . '/module-config-cache.php';
    }

    /**
     * Get the path where cache file(s) are stored
     */
    public function getCacheDir(): ?string
    {
        return $this->cacheDir;
    }

    /**
     * Set the path where cache files can be stored
     *
     * @param string|null $cacheDir the value to be set
     */
    public function setCacheDir(?string $cacheDir): ListenerOptions
    {
        $this->cacheDir = $cacheDir ? static::normalizePath($cacheDir) : null;

        return $this;
    }

    /**
     * Check if the module class map cache is enabled
     */
    public function getModuleMapCacheEnabled(): bool
    {
        return $this->moduleMapCacheEnabled;
    }

    /**
     * Set if the module class map cache should be enabled or not
     */
    public function setModuleMapCacheEnabled(bool $enabled): ListenerOptions
    {
        $this->moduleMapCacheEnabled = (bool) $enabled;
        return $this;
    }

    /**
     * Get key used to create the cache file name
     */
    public function getModuleMapCacheKey(): string
    {
        return (string) $this->moduleMapCacheKey;
    }

    /**
     * Set key used to create the cache file name
     *
     * @param  string $moduleMapCacheKey the value to be set
     */
    public function setModuleMapCacheKey(string $moduleMapCacheKey): ListenerOptions
    {
        $this->moduleMapCacheKey = $moduleMapCacheKey;
        return $this;
    }

    /**
     * Get the path to the module class map cache
     */
    public function getModuleMapCacheFile(): string
    {
        if ($this->getCacheDir() && $this->getModuleMapCacheKey()) {
            return $this->getCacheDir() . '/module-classmap-cache.' . $this->getModuleMapCacheKey() . '.php';
        }

        return $this->getCacheDir() . '/module-classmap-cache.php';
    }

    /**
     * Set whether to check dependencies during module loading or not
     */
    public function getCheckDependencies(): bool
    {
        return $this->checkDependencies;
    }

    /**
     * Set whether to check dependencies during module loading or not
     *
     * @param  bool $checkDependencies the value to be set
     */
    public function setCheckDependencies(bool $checkDependencies): ListenerOptions
    {
        $this->checkDependencies = (bool) $checkDependencies;

        return $this;
    }

    /**
     * Whether or not to use laminas-loader to autoload modules.
     */
    public function useLaminasLoader(): bool
    {
        return $this->useLaminasLoader;
    }

    /**
     * Set a flag indicating if the module manager should use laminas-loader
     *
     * Setting this option to false will disable ModuleAutoloader, requiring
     * other means of autoloading to be used (e.g., Composer).
     *
     * If disabled, the AutoloaderProvider feature will be disabled as well
     */
    public function setUseLaminasLoader(bool $flag): ListenerOptions
    {
        $this->useLaminasLoader = (bool) $flag;
        return $this;
    }

    /**
     * Normalize a path for insertion in the stack
     */
    public static function normalizePath(string $path): string
    {
        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');
        return $path;
    }

    /** @deprecated Use self::useLaminasLoader instead */
    public function useZendLoader(): bool
    {
        return $this->useLaminasLoader(...func_get_args());
    }

    /** @deprecated Use self::setUseLaminasLoader instead */
    public function setUseZendLoader(bool $flag): ListenerOptions
    {
        return $this->setUseLaminasLoader(...func_get_args());
    }
}
