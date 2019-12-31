<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener\Exception;

use Laminas\ModuleManager\Exception;

/**
 * Invalid Argument Exception
 *
 * @category   Laminas
 * @package    Laminas_ModuleManager
 * @subpackage Listener
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
}
