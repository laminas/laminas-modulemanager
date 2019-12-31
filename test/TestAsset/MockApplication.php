<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;

class MockApplication implements ApplicationInterface
{
    public $events;
    public $request;
    public $response;
    public $serviceManager;

    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
    }

    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Get the locator object
     *
     * @return \Laminas\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Get the request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Run the application
     *
     * @return \Laminas\Http\Response
     */
    public function run()
    {
        return $this->response;
    }

    public function bootstrap()
    {
        $event = new MvcEvent();
        $event->setApplication($this);
        $event->setTarget($this);
        $this->getEventManager()->trigger('bootstrap', $event);
    }
}
