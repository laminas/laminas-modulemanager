<?php

namespace Laminas\ModuleManager\Feature;

interface DependencyIndicatorInterface
{
    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array
     */
    public function getModuleDependencies();
}
