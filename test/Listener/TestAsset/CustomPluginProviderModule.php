<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener\TestAsset;

class CustomPluginProviderModule implements CustomPluginProviderInterface
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getCustomPluginConfig()
    {
        return $this->config;
    }
}
