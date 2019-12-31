<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener\TestAsset;

use stdClass;

/**
 * @category   Laminas
 * @package    Laminas_ModuleManager
 * @subpackage UnitTest
 */
class ServiceInvalidReturnModule
{
    public function getServiceConfiguration()
    {
        return new stdClass;
    }
}
