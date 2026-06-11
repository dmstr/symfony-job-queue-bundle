<?php

// file generated with AI assistance: Claude Code - 2025-11-13 10:00:00

namespace Dmstr\SymfonyJobQueue\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'za7_job')]
#[ORM\Index(columns: ['type'])]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Index(columns: ['type', 'status'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    routePrefix: '/admin',
    operations: [
        new Get(),
        new GetCollection(),
    ],
    security: "is_granted('ROLE_USER')",
    normalizationContext: ['groups' => ['job:read']],
    paginationItemsPerPage: 30,
    openapi: new Operation(tags: ['ZA7 Core'])
)]
#[ApiFilter(SearchFilter::class, properties: [
    'type' => 'exact',
    'status' => 'exact',
    'errorMessage' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: ['startedAt', 'completedAt', 'createdAt', 'updatedAt'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'type', 'status', 'progress', 'startedAt', 'completedAt', 'createdAt', 'updatedAt'])]
class Job
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    #[Groups(['job:read'])]
    #[ApiProperty(identifier: true)]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Groups(['job:read'])]
    private string $type;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Groups(['job:read'])]
    private string $status = 'pending';

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['job:read'])]
    private array $inputData = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['job:read'])]
    private ?array $resultData = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['job:read'])]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    #[Groups(['job:read'])]
    private int $progress = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['job:read'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['job:read'])]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['job:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['job:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::v4()->toString();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getInputData(): array
    {
        return $this->inputData;
    }

    public function setInputData(array $inputData): self
    {
        $this->inputData = $inputData;
        return $this;
    }

    public function getResultData(): ?array
    {
        return $this->resultData;
    }

    public function setResultData(?array $resultData): self
    {
        $this->resultData = $resultData;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
