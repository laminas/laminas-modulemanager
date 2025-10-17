<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use Override;

final class CustomPluginProviderModule implements CustomPluginProviderInterface
{
    public function __construct(public array $config)
    {
    }

    #[Override]
    public function getCustomPluginConfig(): array
    {
        return $this->config;
    }
}
