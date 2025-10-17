<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Override;

final class MockApplication implements ApplicationInterface
{
    /** @var EventManagerInterface */
    public $events;
    /** @var mixed */
    public $request;
    /** @var mixed */
    public $response;
    /** @var ServiceLocatorInterface */
    public $serviceManager;

    public function setEventManager(EventManagerInterface $events): void
    {
        $this->events = $events;
    }

    /** {@inheritDoc} */
    #[Override]
    public function getEventManager()
    {
        return $this->events;
    }

    /** {@inheritDoc} */
    #[Override]
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager): self
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /** {@inheritDoc} */
    #[Override]
    public function getRequest()
    {
        return $this->request;
    }

    /** {@inheritDoc} */
    #[Override]
    public function getResponse()
    {
        return $this->response;
    }

    /** {@inheritDoc} */
    #[Override]
    public function run()
    {
        return $this->response;
    }

    public function bootstrap(): void
    {
        $event = new MvcEvent();
        $event->setApplication($this);
        $event->setTarget($this);
        $event->setName(MvcEvent::EVENT_BOOTSTRAP);
        $this->getEventManager()->triggerEvent($event);
    }
}
