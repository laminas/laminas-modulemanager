<?php

namespace Laminas\ModuleManager\Listener;

use Laminas\Loader\AutoloaderFactory;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\ModuleEvent;

use function method_exists;

/**
 * Autoloader listener
 */
class AutoloaderListener extends AbstractListener
{
    /**
     * @param  ModuleEvent $e
     * @return void
     */
    public function __invoke(ModuleEvent $e)
    {
        $module = $e->getModule();
        if (! $module instanceof AutoloaderProviderInterface
            && ! method_exists($module, 'getAutoloaderConfig')
        ) {
            return;
        }
        $autoloaderConfig = $module->getAutoloaderConfig();
        AutoloaderFactory::factory($autoloaderConfig);
    }
}
