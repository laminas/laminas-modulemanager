<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager;

use Laminas\ModuleManager\Exception;
use Laminas\ModuleManager\Listener\ConfigListener;
use Laminas\ModuleManager\ModuleEvent;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Laminas\ModuleManager\ModuleEvent
 */
class ModuleEventTest extends TestCase
{
    /** @var ModuleEvent */
    protected $event;

    protected function setUp(): void
    {
        $this->event = new ModuleEvent();
    }

    public function testCanRetrieveModuleViaGetter()
    {
        $module = new stdClass();
        $this->event->setModule($module);
        $test = $this->event->getModule();
        self::assertSame($module, $test);
    }

    public function testPassingNonObjectToSetModuleRaisesException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->event->setModule('foo');
    }

    public function testCanRetrieveModuleNameViaGetter()
    {
        $moduleName = 'MyModule';
        $this->event->setModuleName($moduleName);
        $test = $this->event->getModuleName();
        self::assertSame($moduleName, $test);
    }

    public function testPassingNonStringToSetModuleNameRaisesException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->event->setModuleName(new stdClass());
    }

    public function testSettingConfigListenerProxiesToParameters()
    {
        $configListener = new ConfigListener();
        $this->event->setConfigListener($configListener);
        $test = $this->event->getParam('configListener');
        self::assertSame($configListener, $test);
    }

    public function testCanRetrieveConfigListenerViaGetter()
    {
        $configListener = new ConfigListener();
        $this->event->setConfigListener($configListener);
        $test = $this->event->getConfigListener();
        self::assertSame($configListener, $test);
    }
}
