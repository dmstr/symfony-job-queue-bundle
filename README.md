<!-- file generated with AI assistance: Claude Code - 2026-10-28 23:10:00 UTC -->

# dmstr/symfony-job-queue-bundle

Persisted job tracking on top of Symfony Messenger. The bundle stores every dispatched job in a Doctrine entity, validates its input against a JSON schema, runs it asynchronously through a Messenger handler and exposes the jobs as a read-only API Platform resource.

- `Job` Doctrine entity with status, payload, timestamps and result
- `JobQueueService::createAndDispatch()` validates the input, persists the job and dispatches it to Messenger
- `JobMessage` / `JobMessageHandler` wrap the asynchronous execution
- Status transitions: `pending → running → done | failed`
- `Job` is exposed as read-only API Platform resource under `/admin/jobs`, guarded by the permission `job:read`

## Installation

    composer require dmstr/symfony-job-queue-bundle

Requires PHP >= 8.5 and Symfony 7. Besides the Symfony packages (framework-bundle, messenger, uid) and Doctrine ORM with DoctrineBundle, the bundle declares `dmstr/openapi-json-schema-bundle` as runtime dependency, because `JobQueueService`, the prune operation and its command validate input through its `InputSchemaValidator`. It pulls `opis/json-schema` and `api-platform/metadata`, `json-schema` and `openapi` in at runtime; applications built on za7-core already have all of this.

## Configuration

The application has to provide four things.

1. Doctrine ORM mapping for the entity directory of the bundle:

        doctrine:
            orm:
                mappings:
                    DmstrSymfonyJobQueue:
                        type: attribute
                        is_bundle: false
                        dir: '%kernel.project_dir%/vendor/dmstr/symfony-job-queue-bundle/src/Entity'
                        prefix: 'Dmstr\SymfonyJobQueue\Entity'

2. API Platform mapping, so the `Job` resource is discovered:

        api_platform:
            mapping:
                paths:
                    - '%kernel.project_dir%/vendor/dmstr/symfony-job-queue-bundle/src/Entity'

3. `Dmstr\OpenApiJsonSchema\OpenApiJsonSchemaBundle` must be registered, because `JobQueueService` uses its `InputSchemaValidator`. The input schemas of the application's operations reach the validator through a resolver tagged `dmstr_openapi_json_schema.input_resolver` (za7-core registers one); without one, creating a job with an operation name throws a `\LogicException`, see [Input validation](#input-validation).

4. Messenger must be enabled, because `JobQueueService` needs `MessageBusInterface`.

## Input validation

`JobQueueService::createAndDispatch(string $type, array $inputData, ?string $operationName = null)` validates the input through `Dmstr\OpenApiJsonSchema\Service\InputSchemaValidator` when an operation name is given. The schema comes from the input-schema resolver chain of openapi-json-schema-bundle (`InputSchemaResolverInterface`, members tagged `dmstr_openapi_json_schema.input_resolver`), the same chain that documents the OpenAPI request body. Validation runs before the job is persisted: on failure no job row is written and no `JobMessage` is dispatched.

| Case | Result |
|---|---|
| no operation name (`null`) | no validation, the job is created |
| valid input | the job is created with the input unchanged |
| empty input `[]` | validated as an empty object |
| invalid input | `UnprocessableEntityHttpException` (HTTP 422), message `Input validation failed for operation "<name>": <errors>` with a compact JSON error list |
| no schema resolved for the operation, or the schema file is unreadable or not valid JSON | `\LogicException('No input schema for operation "<name>"')`, HTTP 500 through the API |
| input that cannot be encoded as JSON (e.g. invalid UTF-8) | `\JsonException` |

Every operation whose name is passed when creating a job therefore needs an input schema; for free-form input, pass no operation name.

## The job resource

`Job` is a read-only API Platform resource with the operations Get and GetCollection, served below the API prefix of the application:

- `GET /admin/jobs` lists jobs
- `GET /admin/jobs/{id}` returns one job

The collection does not offer `order[id]`; a submitted `order[id]` parameter is ignored.

## Permission `job:read`

Both operations require the permission `job:read` (`security: "is_granted('job:read')"`). The permission is declared by the final class `Dmstr\SymfonyJobQueue\Security\JobPermissions` (`JobPermissions::READ`), which the bundle registers in `config/services.yaml` with the container tag `za7_core.permission` and the attribute `permission: job:read`.

The bundle has no config node, no role map and no dependency on za7-core. Who holds the permission is decided by the application: the za7-core `PermissionVoter` collects the tagged permissions and decides through its role map `za7_core.permissions`:

    za7_core:
        permissions:
            job:read: ['<role that may read jobs>']

### Two meanings of the same string

`job:read` is also the Serializer normalization group of the `Job` resource. These are two separate worlds that happen to share a string: as voter attribute it answers "may this user call the endpoint", as serializer group it selects which properties appear in the response. Mapping the permission in `za7_core.permissions` does not change the serialized fields, and the group does not grant access.

### Running without za7-core

Without za7-core the tag is inert. Unless another voter in the application supports the attribute `job:read`, every request to the job resource gets 403 (fail-closed). To use the bundle without za7-core, add a voter for `job:read`.

## Pruning finished jobs

Every job stays in `dmstr_job` with its input and result after it has finished. Three entry points delete the `completed` and `failed` jobs created more than `days` days ago (default 30); `pending` and `running` jobs are never deleted:

| Entry point | Input |
|---|---|
| `POST /api/admin/jobs/prune` (operation `job_prune`, permission `job:prune`) | `{"days": 30, "dryRun": false}`, schema `src/Entity/Job/prune.input.json`, published as the OpenAPI request body |
| `bin/console app:jobs:prune --days=30 [--dry-run]` | validated against the same schema |
| message `Dmstr\SymfonyJobQueue\Message\PruneJobs(days: 30)` | for the application's scheduler, e.g. `RecurringMessage::every('1 day', new PruneJobs())` |

The response (and the dry run) reports `days`, `before` and the number of jobs `deleted` (or to be deleted). Rows are deleted in batches of 1000 ids by `created_at`, the indexed column.

On MySQL the first run on a large table writes every deleted row, JSON payload included, to the binary log; check the free disk space and start with `--dry-run`. InnoDB keeps the freed space in the table file: `OPTIMIZE TABLE dmstr_job` returns it to the file system after a large first run.

The permission `job:prune` is registered for za7-core like `job:read`; without a role map entry it falls back to the za7-core default `[ROLE_ADMIN]`.

## Migrations

If DoctrineMigrationsBundle is registered, the bundle prepends `doctrine_migrations.migrations_paths` with the namespace `Dmstr\SymfonyJobQueue\Migrations`, so `doctrine:migrations:migrate` creates the table `dmstr_job`. Without DoctrineMigrationsBundle nothing is prepended and the schema has to be created by the application.

## Migrating to 0.4.0

1. Upgrade the bundle to 0.4.0 and the runtime to PHP 8.5.
2. Deploy the za7-core release that ships the `PermissionVoter`.
3. Map `job:read` to the desired role in `za7_core.permissions`.
4. Up to 0.3.x the job resource only required an authenticated user with `ROLE_USER`. Applications that so far let ordinary users read jobs either grant the mapped role to them explicitly or build their own scoped endpoint.

Until step 3 is done, everyone gets 403 on `/admin/jobs`. See the [CHANGELOG](./CHANGELOG.md).

## Tests

PHPUnit (^12). The bundle ships a CLI-only Docker Compose runner without a database server; no host PHP is required:

    docker compose run --rm php

It installs the dependencies and runs `vendor/bin/phpunit`, including functional tests with a small test kernel (API Platform, Doctrine on SQLite, a header-based test authenticator `X-Test-Roles`). The same suite runs in CI via `.github/workflows/tests.yml`.

## License

MIT © diemeisterei GmbH
