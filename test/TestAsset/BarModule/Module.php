<?php

declare(strict_types=1);

namespace BarModule;

use Laminas\Config\Config;

class Module
{
    public function getConfig(): Config
    {
        return new Config(include __DIR__ . '/configs/config.php');
    }
}
