<?php

namespace Laminas\ModuleManager\Feature;

interface TranslatorPluginProviderInterface
{
    /**
     * Expected to return \Laminas\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Laminas\ServiceManager\Config
     */
    public function getTranslatorPluginConfig();
}
