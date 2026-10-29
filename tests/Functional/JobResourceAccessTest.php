<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:11:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional;

use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\Tests\Functional\App\TestKernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class JobResourceAccessTest extends WebTestCase
{
    private string $jobId;

    /**
     * @return array<string, array{string, string}> label => [operation, path template]
     */
    public static function operations(): array
    {
        return [
            'collection' => ['collection', '/api/admin/jobs'],
            'item' => ['item', '/api/admin/jobs/{id}'],
        ];
    }

    #[DataProvider('operations')]
    public function testMappedRoleIsAllowed(string $operation, string $path): void
    {
        $client = $this->createClientWithMap();

        $this->request($client, $path, 'ROLE_JOB_TEST_READER');

        self::assertResponseStatusCodeSame(200);
        $this->assertBodyContainsJob($operation, $client);
    }

    /**
     * @return iterable<string, array{string, string, string, array<string, list<string>>|null}>
     */
    public static function forbiddenCases(): iterable
    {
        foreach (self::operations() as $label => [$operation, $path]) {
            yield "$label: only ROLE_USER" => [$operation, $path, 'ROLE_USER', null];
            yield "$label: unmapped ROLE_ADMIN" => [$operation, $path, 'ROLE_ADMIN', null];
            yield "$label: empty map" => [$operation, $path, 'ROLE_JOB_TEST_READER', []];
            yield "$label: permission without roles" => [$operation, $path, 'ROLE_JOB_TEST_READER', ['job:read' => []]];
        }
    }

    /**
     * @param array<string, list<string>>|null $map
     */
    #[DataProvider('forbiddenCases')]
    public function testUnmappedAccessIsForbidden(string $operation, string $path, string $roles, ?array $map): void
    {
        $client = $this->createClientWithMap($map);

        $this->request($client, $path, $roles);

        self::assertResponseStatusCodeSame(403);
    }

    #[DataProvider('operations')]
    public function testRequestWithoutCredentialsIsUnauthorized(string $operation, string $path): void
    {
        $client = $this->createClientWithMap();

        $this->request($client, $path, null);

        self::assertResponseStatusCodeSame(401);
    }

    /**
     * @param array<string, list<string>>|null $permissionMap null keeps the kernel default
     */
    private function createClientWithMap(?array $permissionMap = null): KernelBrowser
    {
        $client = static::createClient(null === $permissionMap ? [] : ['permission_map' => $permissionMap]);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

        $job = (new Job())->setType('test');
        $em->persist($job);
        $em->flush();
        $em->clear();
        $this->jobId = $job->getId();

        return $client;
    }

    private function request(KernelBrowser $client, string $pathTemplate, ?string $roles): void
    {
        $server = ['HTTP_ACCEPT' => 'application/ld+json'];
        if (null !== $roles) {
            $server['HTTP_X_TEST_ROLES'] = $roles;
        }

        $client->request('GET', str_replace('{id}', $this->jobId, $pathTemplate), server: $server);
    }

    private function assertBodyContainsJob(string $operation, KernelBrowser $client): void
    {
        $body = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        if ('item' === $operation) {
            self::assertSame($this->jobId, $body['id']);

            return;
        }

        $members = $body['hydra:member'] ?? $body['member'] ?? [];
        self::assertContains($this->jobId, array_column($members, 'id'));
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(
            $options['environment'] ?? 'test',
            $options['debug'] ?? true,
            $options['permission_map'] ?? TestKernel::DEFAULT_PERMISSION_MAP,
        );
    }
}
