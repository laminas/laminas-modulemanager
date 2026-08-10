<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

interface ConfigMergerInterface
{
    /**
     * @return array
     */
    public function getMergedConfig();

    /**
     * @return ConfigMergerInterface
     */
    public function setMergedConfig(array $config);
}
