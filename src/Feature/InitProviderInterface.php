<?php

namespace Laminas\ModuleManager\Feature;

use Laminas\ModuleManager\ModuleManagerInterface;

interface InitProviderInterface
{
    /**
     * Initialize workflow
     *
     * @param  ModuleManagerInterface $manager
     * @return void
     */
    public function init(ModuleManagerInterface $manager);
}
