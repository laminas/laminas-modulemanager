<?php

namespace BazModule;

use Laminas\Config\Config;

class Module
{
    public function getConfig()
    {
        return new Config(include __DIR__ . '/configs/config.php');
    }
}
