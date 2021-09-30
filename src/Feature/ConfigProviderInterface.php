<?php

namespace Laminas\ModuleManager\Feature;

interface ConfigProviderInterface
{
    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig();
}
