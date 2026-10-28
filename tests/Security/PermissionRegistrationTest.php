<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:13:45 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Security;

use ApiPlatform\Metadata\ApiResource;
use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\JobQueueBundle;
use Dmstr\SymfonyJobQueue\Security\JobPermissions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PermissionRegistrationTest extends TestCase
{
    /** Copied from za7-core. */
    private const PERMISSION_PATTERN = '/^[a-z][a-z0-9]*:[a-z][a-z0-9_]*$/';

    public function testExactlyOneTagWithTheReadPermission(): void
    {
        $tagged = $this->taggedServices();

        self::assertCount(1, $tagged);
        $tags = array_values($tagged)[0];
        self::assertCount(1, $tags);
        $permission = $tags[0]['permission'] ?? null;
        self::assertIsString($permission);
        self::assertSame(JobPermissions::READ, $permission);
        self::assertSame('job:read', $permission);
        self::assertMatchesRegularExpression(self::PERMISSION_PATTERN, $permission);
    }

    public function testResourceSecurityChecksTheRegisteredPermission(): void
    {
        $resource = $this->resource();

        self::assertSame(1, preg_match("/^is_granted\\('([^']+)'\\)$/", (string) $resource->getSecurity(), $m));
        $tags = array_values($this->taggedServices())[0];
        self::assertSame($tags[0]['permission'], $m[1]);
    }

    public function testTagCarrierHasNoConstructor(): void
    {
        // Autowired services with a non-public constructor break the container.
        self::assertNull((new \ReflectionClass(JobPermissions::class))->getConstructor());
    }

    public function testOperationsCarryNoOwnSecurity(): void
    {
        $operations = $this->resource()->getOperations();

        self::assertNotEmpty($operations);
        foreach ($operations as $operation) {
            self::assertNull($operation->getSecurity());
        }
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function taggedServices(): array
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('kernel.environment', 'test');
        $builder->setParameter('kernel.build_dir', sys_get_temp_dir());
        (new JobQueueBundle())->getContainerExtension()->load([], $builder);

        return $builder->findTaggedServiceIds('za7_core.permission');
    }

    private function resource(): ApiResource
    {
        return (new \ReflectionClass(Job::class))->getAttributes(ApiResource::class)[0]->newInstance();
    }
}
