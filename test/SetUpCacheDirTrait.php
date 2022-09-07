<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager;

use function glob;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * Offer common setUp/tearDown methods for configure a common cache dir.
 */
trait SetUpCacheDirTrait
{
    /** @var string */
    protected $tmpdir;

    /** @var string */
    protected $configCache;

    /** @before */
    protected function createTmpDir(): void
    {
        $this->tmpdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laminas_module_cache_dir';
        @mkdir($this->tmpdir);

        $this->configCache = $this->tmpdir . DIRECTORY_SEPARATOR . 'config.cache.php';
    }

    /** @after */
    protected function removeTmpDir(): void
    {
        $file = glob($this->tmpdir . DIRECTORY_SEPARATOR . '*');
        if (isset($file[0])) {
            // change this if there's ever > 1 file
            @unlink($file[0]);
        }
        @rmdir($this->tmpdir);
    }
}
