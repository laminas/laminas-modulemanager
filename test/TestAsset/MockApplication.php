<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;

class MockApplication implements ApplicationInterface
{
    /** @var EventManagerInterface */
    public $events;
    /** @var mixed */
    public $request;
    /** @var mixed */
    public $response;
    /** @var ServiceLocatorInterface */
    public $serviceManager;

    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
    }

    /** {@inheritDoc} */
    public function getEventManager()
    {
        return $this->events;
    }

    /** {@inheritDoc} */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /** @param ServiceLocatorInterface $serviceManager */
    public function setServiceManager($serviceManager): self
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /** {@inheritDoc} */
    public function getRequest()
    {
        return $this->request;
    }

    /** {@inheritDoc} */
    public function getResponse()
    {
        return $this->response;
    }

    /** {@inheritDoc} */
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
