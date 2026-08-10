<?php

declare(strict_types=1);

namespace LaminasTest\ModuleManager\Listener;

use ArrayObject;
use InvalidArgumentException;
use Laminas\ModuleManager\Listener\ListenerOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function strstr;

#[CoversClass(ListenerOptions::class)]
final class ListenerOptionsTest extends TestCase
{
    public function testCanConfigureWithArrayInConstructor(): void
    {
        $options = new ListenerOptions([
            'cache_dir'            => __DIR__,
            'config_cache_enabled' => true,
            'config_cache_key'     => 'foo',
            'module_paths'         => ['module', 'paths'],
            'config_glob_paths'    => ['glob', 'paths'],
            'config_static_paths'  => ['static', 'custom_paths'],
        ]);
        self::assertSame($options->getCacheDir(), __DIR__);
        self::assertTrue($options->getConfigCacheEnabled());
        self::assertNotNull(strstr($options->getConfigCacheFile(), __DIR__));
        self::assertNotNull(strstr($options->getConfigCacheFile(), '.php'));
        self::assertSame('foo', $options->getConfigCacheKey());
        self::assertSame(['module', 'paths'], $options->getModulePaths());
        self::assertSame(['glob', 'paths'], $options->getConfigGlobPaths());
        self::assertSame(['static', 'custom_paths'], $options->getConfigStaticPaths());
    }

    public function testConfigCacheFileWithEmptyCacheKey(): void
    {
        $options = new ListenerOptions([
            'cache_dir'            => __DIR__,
            'config_cache_enabled' => true,
            'module_paths'         => ['module', 'paths'],
            'config_glob_paths'    => ['glob', 'paths'],
            'config_static_paths'  => ['static', 'custom_paths'],
        ]);

        self::assertEquals(__DIR__ . '/module-config-cache.php', $options->getConfigCacheFile());
        $options->setConfigCacheKey('foo');
        self::assertEquals(__DIR__ . '/module-config-cache.foo.php', $options->getConfigCacheFile());
    }

    public function testModuleMapCacheFileWithEmptyCacheKey(): void
    {
        $options = new ListenerOptions([
            'cache_dir'                => __DIR__,
            'module_map_cache_enabled' => true,
            'module_paths'             => ['module', 'paths'],
            'config_glob_paths'        => ['glob', 'paths'],
            'config_static_paths'      => ['static', 'custom_paths'],
        ]);

        self::assertEquals(__DIR__ . '/module-classmap-cache.php', $options->getModuleMapCacheFile());
        $options->setModuleMapCacheKey('foo');
        self::assertEquals(__DIR__ . '/module-classmap-cache.foo.php', $options->getModuleMapCacheFile());
    }

    public function testCanAccessKeysAsProperties(): void
    {
        $options = new ListenerOptions([
            'cache_dir'            => __DIR__,
            'config_cache_enabled' => true,
            'config_cache_key'     => 'foo',
            'module_paths'         => ['module', 'paths'],
            'config_glob_paths'    => ['glob', 'paths'],
            'config_static_paths'  => ['static', 'custom_paths'],
        ]);
        self::assertSame($options->cache_dir, __DIR__);
        $options->cache_dir = 'foo';
        self::assertSame($options->cache_dir, 'foo');
        self::assertTrue(isset($options->cache_dir));
        unset($options->cache_dir);
        self::assertFalse(isset($options->cache_dir));

        self::assertTrue($options->config_cache_enabled);
        $options->config_cache_enabled = false;
        self::assertFalse($options->config_cache_enabled);
        self::assertEquals('foo', $options->config_cache_key);
        self::assertSame(['module', 'paths'], $options->module_paths);
        self::assertSame(['glob', 'paths'], $options->config_glob_paths);
        self::assertSame(['static', 'custom_paths'], $options->config_static_paths);
    }

    public function testSetModulePathsAcceptsTraversable(): void
    {
        $config  = new ArrayObject([__DIR__]);
        $options = new ListenerOptions();
        $options->setModulePaths($config);
        self::assertSame($config, $options->getModulePaths());
    }

    public function testSetModulePathsThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $options = new ListenerOptions();
        $options->setModulePaths('asd');
    }

    public function testSetConfigGlobPathsAcceptsTraversable(): void
    {
        $config  = new ArrayObject([__DIR__]);
        $options = new ListenerOptions();
        $options->setConfigGlobPaths($config);
        self::assertSame($config, $options->getConfigGlobPaths());
    }

    public function testSetConfigGlobPathsThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $options = new ListenerOptions();
        $options->setConfigGlobPaths('asd');
    }

    public function testSetConfigStaticPathsThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $options = new ListenerOptions();
        $options->setConfigStaticPaths('asd');
    }

    public function testSetExtraConfigAcceptsArrayOrTraversable(): void
    {
        $array       = [__DIR__];
        $traversable = new ArrayObject($array);
        $options     = new ListenerOptions();

        self::assertSame($options, $options->setExtraConfig($array));
        self::assertSame($array, $options->getExtraConfig());

        self::assertSame($options, $options->setExtraConfig($traversable));
        self::assertSame($traversable, $options->getExtraConfig());
    }

    public function testSetExtraConfigThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $options = new ListenerOptions();
        $options->setExtraConfig('asd');
    }
}
