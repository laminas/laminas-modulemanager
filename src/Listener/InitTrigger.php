<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\ModuleEvent;

use function method_exists;

class InitTrigger extends AbstractListener
{
    public function __invoke(ModuleEvent $e): void
    {
        $module = $e->getModule();

        if ($module === null) {
            return;
        }

        if (
            ! $module instanceof InitProviderInterface
            && ! method_exists($module, 'init')
        ) {
            return;
        }

        $module->init($e->getTarget());
    }
}
