# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-10-26
### Added
- Drupal 12 support.

### Fixed
- Mark the Twig nodes as `#[YieldReady]`, so templates compile when Twig's `use_yield` option is enabled (the default since Drupal 11.3)
- Fix the `Twig\Node\Node` instantiation deprecation in `DebugNodeVisitor` on Twig 3.15+
- Only add `template_preprocess()` to the preprocess functions of the theme hooks before Drupal 11.2. Since Drupal 11.2 core sets the default template variables itself, the function is deprecated and it is removed in Drupal 12

### Removed
- Support for Drupal before 11.4 and PHP before 8.2. The module now requires Drupal `^11.4 || ^12` and PHP 8.2 or
  higher.

## [1.0.2] - 2024-08-12
### Added
- Add Drupal 11 support

## [1.0.1] - 2024-01-07
### Fixed
- Update `drupal/core` version in composer.json

## [1.0.0] - 2024-01-07
### Added
- Add Drupal 10 support

### Fixed
- Fix `core_version_requirement` & add `php` requirement in info.yml
- Fix signature of `ViewBuilder::addCacheContexts` & add `ViewBuilder::addCacheContext`
 
## [1.0.0-alpha2] - 2022-06-28
### Changed
- Register template paths in themes and modules through their info.yml files.

## [1.0.0-alpha1] - 2022-06-28
First tagged version.
