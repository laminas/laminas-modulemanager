<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

use function get_include_path;
use function in_array;
use function is_array;
use function set_include_path;
use function spl_autoload_functions;
use function spl_autoload_unregister;

/**
 * Offer common setUp/tearDown methods for preserve current autoload functions and include paths.
 */
trait ResetAutoloadFunctionsTrait
{
    /** @var callable[] */
    private $loaders;

    /** @var string */
    private $includePath;

    #[Before]
    protected function preserveAutoloadFunctions(): void
    {
        $this->loaders = spl_autoload_functions();
        if (! is_array($this->loaders)) {
            // spl_autoload_functions does not return an empty array when no
            // autoloaders are registered...
            $this->loaders = [];
        }
    }

    #[Before]
    protected function preserveIncludePath(): void
    {
        $this->includePath = get_include_path();
    }

    #[After]
    protected function restoreAutoloadFunctions(): void
    {
        $loaders = spl_autoload_functions();
        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                if (! in_array($loader, $this->loaders, true)) {
                    spl_autoload_unregister($loader);
                }
            }
        }
    }

    #[Before]
    protected function restoreIncludePath(): void
    {
        set_include_path((string) $this->includePath);
    }
}
