<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Feature;

use Laminas\EventManager\EventInterface;

/**
 * Boostrap listener provider interface
 *
 * @category   Laminas
 * @package    Laminas_ModuleManager
 * @subpackage Feature
 */
interface BootstrapListenerInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @return array
     */
    public function onBootstrap(EventInterface $e);
}
