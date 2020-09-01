# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.11.0 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.10.1 - 2020-09-01

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#17](https://github.com/laminas/laminas-modulemanager/pull/17) removes an extraneous ";" character from generated cache files.


-----

### Release Notes for [2.10.1](https://github.com/laminas/laminas-modulemanager/milestone/4)

next bugfix release (mini)

### 2.10.1

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [17: Fix superfluous traling semicolon in cached config](https://github.com/laminas/laminas-modulemanager/pull/17) thanks to @bcremer
## 2.10.0 - 2020-08-25

### Added

- [#15](https://github.com/laminas/laminas-modulemanager/pull/15) adds support for caching closures when caching configuration.

- [#16](https://github.com/laminas/laminas-modulemanager/pull/16) adds support for the upcoming PHP 8.0 release.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#8](https://github.com/laminas/laminas-modulemanager/pull/8) removes support for PHP versions prior to PHP 7.3.

- [#16](https://github.com/laminas/laminas-modulemanager/pull/16) removes support for v2 releases (deprecated) of the following components:
  - laminas-config
  - laminas-eventmanger
  - laminas-servicemanager
  - laminas-stdlib

### Fixed

- Nothing.

## 2.9.0 - 2020-08-25

### Added

- [#9](https://github.com/laminas/laminas-modulemanager/pull/9) adds [webimpress/safe-writer](https://github.com/webimpress/safe-writer) for saving cache files safely to avoid race conditions when the same file is written multiple times in a short time period. 

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.4 - 2019-10-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#91](https://github.com/zendframework/zend-modulemanager/pull/91) fixes permission on cache file.
  The permission denied issue occurs on reading the cache file, when cache has been build using different user account.

## 2.8.3 - 2019-10-18

### Added

- [zendframework/zend-modulemanager#85](https://github.com/zendframework/zend-modulemanager/pull/85) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#88](https://github.com/zendframework/zend-modulemanager/pull/88) fixes
  how cache files are created. Temporary file is created first and when
  completed it is renamed to target file. It prevents from loading uncompleted
  files.

## 2.8.2 - 2017-12-02

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#74](https://github.com/zendframework/zend-modulemanager/pull/74) fixes
  exception message in ConfigListener

## 2.8.1 - 2017-11-01

### Added

- Nothing.

### Changed

- [zendframework/zend-modulemanager#73](https://github.com/zendframework/zend-modulemanager/pull/73) modifies
  the `ModuleResolverListener` slightly. In
  [zendframework/zend-modulemanager#5](https://github.com/zendframework/zend-modulemanager/pull/5),
  released in 2.8.0, we added the ability to use classes named after the module
  itself as a module class. However, in some specific cases, primarily when the
  module is a top-level namespace, this can lead to conflicts with
  globally-scoped classes. The patch in this release modifies the logic to first
  check if a `Module` class exists under the module namespace, and will use
  that; otherwise, it will then check if a class named after the namespace
  exists. Additionally, the class now implements a blacklist of specific classes
  known to be non-instantiable, including the `Generator` class shipped with the
  PHP language itself.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.0 - 2017-07-11

### Added

- [zendframework/zend-modulemanager#4](https://github.com/zendframework/zend-modulemanager/pull/4) adds a new
  `ListenerOptions` option, `use_laminas_loader`. The option defaults to `true`,
  which keeps the current behavior of registering the `ModuleAutoloader` and
  `AutoloaderProvider`. If you disable it, these features will no longer be
  loaded, allowing `ModuleManager` to be used without laminas-loader.
- [zendframework/zend-modulemanager#5](https://github.com/zendframework/zend-modulemanager/pull/5) adds the
  ability to use a class of any name for a module, so long as you provide the
  fully qualified class name when registering the module with the module
  manager.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-modulemanager#62](https://github.com/zendframework/zend-modulemanager/pull/62) removes
  support for PHP 5.5 and HHVM.

### Fixed

- [zendframework/zend-modulemanager#53](https://github.com/zendframework/zend-modulemanager/pull/53) preventing race conditions
  when writing cache files (merged configuration)

## 2.7.3 - 2017-07-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#39](https://github.com/zendframework/zend-modulemanager/pull/39) and
  [zendframework/zend-modulemanager#53](https://github.com/zendframework/zend-modulemanager/pull/53) prevent
  race conditions when writing cache files (merged configuration).
- [zendframework/zend-modulemanager#36](https://github.com/zendframework/zend-modulemanager/pull/36) removes a
  throw from `ServiceListener::onLoadModulesPost()` that was previously emitted
  when a named plugin manager did not have an associated service present yet.
  Doing so allows plugin managers to be registered after configuration is fully
  merged, instead of requiring they be defined early. This change allows
  components to define their plugin managers via their `Module` classes.
- [zendframework/zend-modulemanager#58](https://github.com/zendframework/zend-modulemanager/pull/58) corrects
  the typehint for the `ServiceListener::$listeners` property.

## 2.7.2 - 2016-05-16

### Added

- [zendframework/zend-modulemanager#38](https://github.com/zendframework/zend-modulemanager/pull/38) prepares
  and publishes the documentation to https://docs.laminas.dev/laminas-modulemanager/
- [zendframework/zend-modulemanager#40](https://github.com/zendframework/zend-modulemanager/pull/40) adds a
  requirement on laminas-config. Since the default use case centers around config
  merging and requires the component, it should be required by
  laminas-modulemanager.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.1 - 2016-02-27

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#31](https://github.com/zendframework/zend-modulemanager/pull/31) updates the
  `ServiceListener:onLoadModulesPost()` workflow to override existing services
  on a given service/plugin manager instance when configuring it. Since the
  listener operates as part of bootstrapping, this is a requirement.

## 2.7.0 - 2016-02-25

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#13](https://github.com/zendframework/zend-modulemanager/pull/13) and
  [zendframework/zend-modulemanager#28](https://github.com/zendframework/zend-modulemanager/pull/28) update the
  component to be forwards-compatible with laminas-servicemanager v3. This
  primarily affects how configuration is aggregated within the
  `ServiceListener` (as v3 has a dedicated method in the
  `Laminas\ServiceManager\ConfigInterface` for retrieving it).

- [zendframework/zend-modulemanager#12](https://github.com/zendframework/zend-modulemanager/pull/12),
  [zendframework/zend-modulemanager#28](https://github.com/zendframework/zend-modulemanager/pull/28), and
  [zendframework/zend-modulemanager#29](https://github.com/zendframework/zend-modulemanager/pull/29) update the
  component to be forwards-compatible with laminas-eventmanager v3. Primarily, this
  involves:
  - Changing trigger calls to `triggerEvent()` and/or `triggerEventUntil()`, and
    ensuring the event instance is injected with the new event name prior.
  - Ensuring aggregates are attached using the `$aggregate->attach($events)`
    signature instead of the `$events->attachAggregate($aggregate)` signature.
  - Using laminas-eventmanager's `EventListenerIntrospectionTrait` to test that
    listeners are attached at expected priorities.

## 2.6.1 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed a condition where the `ModuleEvent` target was not properly populated
  with the `ModuleManager` as the target.

## 2.6.0 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#10](https://github.com/zendframework/zend-modulemanager/pull/10) pins the
  laminas-stdlib version to `~2.7`, allowing it to use that version forward, and
  ensuring compatibility with consumers of the new laminas-hydrator library.

## 2.5.3 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed a condition where the `ModuleEvent` target was not properly populated
  with the `ModuleManager` as the target.

## 2.5.2 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-modulemanager#9](https://github.com/zendframework/zend-modulemanager/pull/9) pins the
  laminas-stdlib version to `>=2.5.0,<2.7.0`, as 2.7.0 deprecates the hydrators (in
  favor of the new laminas-hydrator library).
