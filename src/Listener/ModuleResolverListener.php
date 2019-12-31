<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

/**
 * Module resolver listener
 *
 * @category   Laminas
 * @package    Laminas_ModuleManager
 * @subpackage Listener
 */
class ModuleResolverListener extends AbstractListener
{
    /**
     * @param  \Laminas\EventManager\EventInterface $e
     * @return object
     */
    public function __invoke($e)
    {
        $moduleName = $e->getModuleName();
        $class      = $moduleName . '\Module';

        if (!class_exists($class)) {
            return false;
        }

        $module = new $class;
        return $module;
    }
}
