<?php

namespace Laminas\ModuleManager\Feature;

use Laminas\EventManager\EventInterface;

/**
 * Bootstrap listener provider interface
 */
interface BootstrapListenerInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return void
     */
    public function onBootstrap(EventInterface $e);
}
