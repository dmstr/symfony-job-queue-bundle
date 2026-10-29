<!-- file generated with AI assistance: Claude Code - 2026-10-28 23:10:00 UTC -->

# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html). While the major version is 0, a minor release may contain breaking changes.

## [0.5.0] - 2026-10-29

### Changed

- **BREAKING:** the bundle class is renamed from `Dmstr\SymfonyJobQueue\JobQueueBundle` to `Dmstr\SymfonyJobQueue\SymfonyJobQueueBundle`. Symfony Flex registers a bundle without its own recipe only under the class name it derives from the PSR-4 namespace (`Dmstr\SymfonyJobQueue` → `SymfonyJobQueueBundle`); under the old name a new application never got the bundle in `config/bundles.php`, and with the za7-core default permission map the container build then failed with `Unknown permissions`. The configuration key and all services are unchanged.

### Migration

Replace `Dmstr\SymfonyJobQueue\JobQueueBundle::class` with `Dmstr\SymfonyJobQueue\SymfonyJobQueueBundle::class` in `config/bundles.php`. There is no alias for the old name.

## [0.4.0] - 2026-10-28

### Changed

- **BREAKING:** the job resource requires the permission `job:read` instead of `ROLE_USER`. Without a voter and a role map (za7-core `PermissionVoter` with `za7_core.permissions`) everyone gets 403.
- **BREAKING:** PHP >= 8.5.
- Serializer `Attribute\Groups` replaces the deprecated `Annotation\Groups`.

### Added

- `JobPermissions` with the permission `job:read`, registered with the container tag `za7_core.permission`.
- Runtime dependencies `dmstr/openapi-json-schema-bundle` and `opis/json-schema` are declared; `JobQueueService` already used them.

### Fixed

- The `doctrine_migrations` prepend no longer throws when DoctrineMigrationsBundle is not registered.
- `order[id]` is no longer offered on the job collection; a submitted parameter is ignored (#9).

## [0.3.0] - 2026-07-13

### Changed

- **BREAKING:** table `za7_job` renamed to `dmstr_job`, with explicit index names.

## [0.2.1] - 2026-07-01

### Fixed

- Portable migrations for PostgreSQL (#5).

## [0.2.0] - 2026-06-14

### Changed

- PHP >= 8.4 (#4).
- Back to the OpenAPI tag "System" (#3).

### Added

- Unit tests, Compose runner and GitHub Action (#2).

## [0.1.1] - 2026-06-11

### Changed

- OpenAPI tag "System" renamed to "ZA7 Core".

## [0.1.0] - 2026-06-10

### Added

- Initial release.

[0.4.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.1.1...0.2.0
[0.1.1]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/dmstr/symfony-job-queue-bundle/releases/tag/0.1.0
