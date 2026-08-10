<?php

declare(strict_types=1);

namespace BazModule;

use ArrayObject;

final class Module
{
    public function getConfig(): ArrayObject
    {
        return new ArrayObject(include __DIR__ . '/configs/config.php');
    }
}
