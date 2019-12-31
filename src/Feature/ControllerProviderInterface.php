<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Feature;

/**
 * @category   Laminas
 * @package    Laminas_ModuleManager
 * @subpackage Feature
 */
interface ControllerProviderInterface
{
    /**
     * Expected to return \Laminas\ServiceManager\Configuration object or array to
     * seed such an object.
     *
     * @return array|\Laminas\ServiceManager\Configuration
     */
    public function getControllerConfig();
}
