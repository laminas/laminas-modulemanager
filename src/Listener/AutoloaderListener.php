<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

use Laminas\Loader\AutoloaderFactory;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\ModuleEvent;

use function method_exists;

class AutoloaderListener extends AbstractListener
{
    public function __invoke(ModuleEvent $e): void
    {
        $module = $e->getModule();

        if ($module === null) {
            return;
        }

        if (
            ! $module instanceof AutoloaderProviderInterface
            && ! method_exists($module, 'getAutoloaderConfig')
        ) {
            return;
        }

        $autoloaderConfig = $module->getAutoloaderConfig();
        AutoloaderFactory::factory($autoloaderConfig);
    }
}
