# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
