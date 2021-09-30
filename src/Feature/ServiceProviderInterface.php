<?php

namespace Laminas\ModuleManager\Feature;

interface ServiceProviderInterface
{
    /**
     * Expected to return \Laminas\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Laminas\ServiceManager\Config
     */
    public function getServiceConfig();
}
