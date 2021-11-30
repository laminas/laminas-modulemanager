<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;

use function method_exists;

class OnBootstrapListener extends AbstractListener
{
    public function __invoke(ModuleEvent $e): void
    {
        $module = $e->getModule();

        if ($module === null) {
            return;
        }

        if (
            ! $module instanceof BootstrapListenerInterface
            && ! method_exists($module, 'onBootstrap')
        ) {
            return;
        }

        $moduleManager = $e->getTarget();
        $events        = $moduleManager->getEventManager();
        $sharedEvents  = $events->getSharedManager();
        $sharedEvents->attach(Application::class, ModuleManager::EVENT_BOOTSTRAP, [$module, 'onBootstrap']);
    }
}
