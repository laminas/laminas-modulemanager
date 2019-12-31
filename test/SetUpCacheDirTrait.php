<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager;

/**
 * Offer common setUp/tearDown methods for configure a common cache dir.
 */
trait SetUpCacheDirTrait
{
    /**
     * @var string
     */
    protected $tmpdir;

    /**
     * @var string
     */
    protected $configCache;

    /**
     * @before
     */
    protected function createTmpDir()
    {
        $this->tmpdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laminas_module_cache_dir';
        @mkdir($this->tmpdir);

        $this->configCache = $this->tmpdir . DIRECTORY_SEPARATOR . 'config.cache.php';
    }

    /**
     * @after
     */
    protected function removeTmpDir()
    {
        $file = glob($this->tmpdir . DIRECTORY_SEPARATOR . '*');
        @unlink($file[0]); // change this if there's ever > 1 file
        @rmdir($this->tmpdir);
    }
}
