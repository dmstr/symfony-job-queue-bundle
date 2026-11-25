<!-- file generated with AI assistance: Claude Code - 2026-10-28 23:10:00 UTC -->

# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html). While the major version is 0, a minor release may contain breaking changes.

## [0.7.0] - 2026-11-25

### Changed

- `JobQueueService::createAndDispatch()` validates the input through `InputSchemaValidator` of openapi-json-schema-bundle instead of its own copy of the validation (#8). As before, validation runs only when an operation name is given, and before the job is persisted and dispatched; on failure no job row is written and no `JobMessage` is dispatched.
- **BREAKING:** an operation name without an input schema throws `\LogicException('No input schema for operation "…"')` (HTTP 500 through the API, no job created) instead of silently skipping validation. `\LogicException` is not a `\RuntimeException`, so callers catching `\RuntimeException` do not catch this case.
- **BREAKING:** invalid input throws `UnprocessableEntityHttpException` (HTTP 422) instead of `BadRequestHttpException` (HTTP 400). The message keeps the prefix `Input validation failed for operation "%s": `; the error list after it is compact JSON instead of pretty-printed, and `/` is no longer escaped (`"/days"` instead of `"\/days"`), which matters to clients parsing the message text.
- **BREAKING:** the schema is resolved through the whole resolver chain of openapi-json-schema-bundle (`InputSchemaResolverInterface`, members tagged `dmstr_openapi_json_schema.input_resolver`), so application resolvers apply as well, instead of only the file-based convention of `OperationInputSchemaResolver`.
- A schema file that cannot be read or is not valid JSON counts as "no schema" (`\LogicException`) instead of throwing `\RuntimeException('Invalid JSON schema file: …')`.
- Empty input `[]` is validated as an empty JSON object instead of an empty list.
- Non-empty input that cannot be encoded as JSON (e.g. strings with invalid UTF-8) throws `\JsonException`, because the validator encodes the input with `JSON_THROW_ON_ERROR`.
- `JobQueueService` takes `InputSchemaValidator` instead of `OperationInputSchemaResolver` as third constructor argument (autowired); the bundle no longer needs a service `OperationInputSchemaResolver` registered under its class name.
- Requires `dmstr/openapi-json-schema-bundle` `^0.5.0 || ^0.6.0`. `opis/json-schema` is no longer a direct requirement; it comes in through openapi-json-schema-bundle.

### Migration

Provide an input schema, through a resolver tagged `dmstr_openapi_json_schema.input_resolver`, for every operation whose name is passed to `createAndDispatch()`, or pass no operation name for free-form input. Switch clients and tests that expect 400 on invalid input to 422, and adjust anything that parses the error list in the message or catches `\RuntimeException` around job creation.

## [0.6.1] - 2026-11-25

### Fixed

- A PHP `\Error` thrown by a job processor (a `\TypeError`, for example) now marks the job as `failed` with `errorMessage` and `completedAt`, instead of leaving it in `running`; the error is still re-thrown, so Messenger retry and the failure transport work as before, and handling of `\Exception` is unchanged (#7).

## [0.6.0] - 2026-11-24

### Added

- Pruning of finished jobs: `POST /api/admin/jobs/prune` (operation `job_prune`, input schema `src/Entity/Job/prune.input.json` with `days`, default 30, and `dryRun`), its CLI mirror `app:jobs:prune --days --dry-run` and the message `PruneJobs` for a scheduler. Deletes `completed` and `failed` jobs created more than `days` days ago in batches; `pending` and `running` jobs stay.
- Permission `job:prune`, registered for za7-core next to `job:read`.
- The bundle registers its own input-schema resolver (`dmstr_job_queue.input_schema_resolver`) for the schemas next to its entity.

### Changed

- Requires `dmstr/openapi-json-schema-bundle` ^0.5.0 (`InputSchemaValidator`).

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

[0.7.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.6.1...0.7.0
[0.6.1]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.6.0...0.6.1
[0.6.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.5.0...0.6.0
[0.5.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.1.1...0.2.0
[0.1.1]: https://github.com/dmstr/symfony-job-queue-bundle/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/dmstr/symfony-job-queue-bundle/releases/tag/0.1.0
