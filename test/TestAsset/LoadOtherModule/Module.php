<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LoadOtherModule;


class Module
{
    public function init($moduleManager)
    {
        $moduleManager->loadModule('BarModule');
    }

    public function getConfig()
    {
        return array('loaded' => 'oh, yeah baby!');
    }
}
