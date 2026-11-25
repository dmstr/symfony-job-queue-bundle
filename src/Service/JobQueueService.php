<?php
// file generated with AI assistance: Claude Code - 2026-05-12 15:20:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Service;

use Dmstr\OpenApiJsonSchema\Service\InputSchemaValidator;
use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\Message\JobMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class JobQueueService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly InputSchemaValidator $validator,
    ) {
    }

    /**
     * Create a job and dispatch it to the queue.
     *
     * When an operation name is given, the input is validated through
     * {@see InputSchemaValidator} against the schema the input-schema resolver
     * chain of openapi-json-schema-bundle resolves for that operation, before
     * the job is persisted or dispatched. Without an operation name the input
     * is not validated.
     *
     * @param string              $type          Job type (e.g. "project_scan").
     * @param array<string,mixed> $inputData     Decoded request body.
     * @param string|null         $operationName API Platform operation name
     *                                           (e.g. "ref_project_scan").
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException when the input violates the schema (HTTP 422)
     * @throws \LogicException when no input schema is resolved for the operation
     * @throws \JsonException when the input cannot be encoded as JSON (e.g. invalid UTF-8)
     */
    public function createAndDispatch(string $type, array $inputData, ?string $operationName = null): Job
    {
        if (null !== $operationName) {
            $this->validator->validate($operationName, $inputData);
        }

        $job = new Job();
        $job->setType($type);
        $job->setInputData($inputData);
        $job->setStatus('pending');

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new JobMessage($job->getId()));

        return $job;
    }
}
