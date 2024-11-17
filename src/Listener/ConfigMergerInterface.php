<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

interface ConfigMergerInterface
{
    /**
     * @param  bool $returnConfigAsObject
     * @return mixed
     */
    public function getMergedConfig($returnConfigAsObject = true);

    /**
     * @return ConfigMergerInterface
     */
    public function setMergedConfig(array $config);
}
