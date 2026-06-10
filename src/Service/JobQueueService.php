<?php
// file generated with AI assistance: Claude Code - 2026-05-12 15:20:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Service;

use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\Message\JobMessage;
use Dmstr\OpenApiJsonSchema\Service\OperationInputSchemaResolver;
use Doctrine\ORM\EntityManagerInterface;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class JobQueueService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly OperationInputSchemaResolver $schemaResolver,
    ) {
    }

    /**
     * Create a job and dispatch it to the queue.
     *
     * @param string                $type          Job type (e.g. "project_scan").
     * @param array<string,mixed>   $inputData     Decoded request body.
     * @param string|null           $operationName API Platform operation name
     *                                             (e.g. "ref_project_scan").
     *                                             When provided, the input is
     *                                             validated against the matching
     *                                             JSON-Schema in `config/schemas/`.
     *
     * @throws \InvalidArgumentException on validation failure.
     */
    public function createAndDispatch(string $type, array $inputData, ?string $operationName = null): Job
    {
        if (null !== $operationName) {
            $this->validateAgainstOperationSchema($operationName, $inputData);
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

    /**
     * @param array<string,mixed> $data
     */
    private function validateAgainstOperationSchema(string $operationName, array $data): void
    {
        $schemaFile = $this->schemaResolver->getSchemaFile($operationName);
        if (null === $schemaFile) {
            // Operation has no input schema — skip validation (caller decided
            // the body is free-form).
            return;
        }

        $schema = json_decode((string) file_get_contents($schemaFile));
        if (!\is_object($schema)) {
            throw new \RuntimeException(sprintf('Invalid JSON schema file: %s', $schemaFile));
        }

        $validator = new Validator();
        $result = $validator->validate(json_decode(json_encode($data)), $schema);

        if (!$result->isValid()) {
            $errors = (new ErrorFormatter())->format($result->error());

            throw new BadRequestHttpException(sprintf(
                'Input validation failed for operation "%s": %s',
                $operationName,
                json_encode($errors, JSON_PRETTY_PRINT),
            ));
        }
    }
}
