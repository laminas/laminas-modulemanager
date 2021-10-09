<?php

declare(strict_types=1);

namespace BadConfigModule;

class Module
{
    public function getConfig(): string
    {
        return 'string';
    }
}
