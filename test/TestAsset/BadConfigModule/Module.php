<?php

declare(strict_types=1);

namespace BadConfigModule;

final class Module
{
    public function getConfig(): string
    {
        return 'string';
    }
}
