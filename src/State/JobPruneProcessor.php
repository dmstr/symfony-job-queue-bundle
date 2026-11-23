<?php
// file generated with AI assistance: Claude Code - 2026-11-23 22:50:06 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Dmstr\OpenApiJsonSchema\Service\InputSchemaValidator;
use Dmstr\SymfonyJobQueue\Dto\PruneResult;
use Dmstr\SymfonyJobQueue\Service\JobPruner;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * POST /admin/jobs/prune: validates the body against
 * `Entity/Job/prune.input.json` and deletes the finished jobs older than
 * `days`.
 *
 * @implements ProcessorInterface<mixed, PruneResult>
 */
final class JobPruneProcessor implements ProcessorInterface
{
    public const string OPERATION = 'job_prune';

    public function __construct(
        private readonly InputSchemaValidator $validator,
        private readonly JobPruner $pruner,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PruneResult
    {
        $input = $this->validator->validateRaw(
            self::OPERATION,
            $this->requestStack->getCurrentRequest()?->getContent() ?? ''
        );

        return $this->pruner->prune(
            (int) ($input['days'] ?? JobPruner::DEFAULT_DAYS),
            (bool) ($input['dryRun'] ?? false)
        );
    }
}
