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

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
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
        $event->setName(MvcEvent::EVENT_BOOTSTRAP);
        $this->getEventManager()->triggerEvent($event);
    }
}
