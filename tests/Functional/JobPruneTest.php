<?php
// file generated with AI assistance: Claude Code - 2026-11-23 22:50:48 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional;

use Dmstr\OpenApiJsonSchema\Interface\InputSchemaResolverInterface;
use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\State\JobPruneProcessor;
use Dmstr\SymfonyJobQueue\Tests\Functional\App\TestKernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class JobPruneTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $em = $this->entityManager();
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

        // [status, age in days]
        foreach ([['completed', 40], ['failed', 40], ['pending', 40], ['running', 40], ['completed', 5]] as [$status, $age]) {
            $job = (new Job())->setType('test')->setStatus($status);
            $em->persist($job);
            $em->flush();
            $em->getConnection()->executeStatement(
                'UPDATE dmstr_job SET created_at = ? WHERE id = ?',
                [(new \DateTimeImmutable("-$age days"))->format('Y-m-d H:i:s'), $job->getId()]
            );
        }
        $em->clear();
    }

    public function testDeletesFinishedJobsOlderThanDays(): void
    {
        $body = $this->post('{"days": 30}', 'ROLE_JOB_TEST_PRUNER');

        self::assertResponseStatusCodeSame(200);
        self::assertSame(2, $body['deleted']);
        self::assertSame(30, $body['days']);
        self::assertFalse($body['dryRun']);
        self::assertSame(['completed', 'pending', 'running'], $this->remainingStatuses());
    }

    public function testDryRunOnlyCounts(): void
    {
        $body = $this->post('{"days": 30, "dryRun": true}', 'ROLE_JOB_TEST_PRUNER');

        self::assertResponseStatusCodeSame(200);
        self::assertSame(2, $body['deleted']);
        self::assertTrue($body['dryRun']);
        self::assertCount(5, $this->remainingStatuses());
    }

    public function testEmptyBodyUsesThirtyDays(): void
    {
        $body = $this->post('', 'ROLE_JOB_TEST_PRUNER');

        self::assertResponseStatusCodeSame(200);
        self::assertSame(30, $body['days']);
        self::assertSame(2, $body['deleted']);
    }

    public function testInvalidDaysAreRejected(): void
    {
        $this->post('{"days": 0}', 'ROLE_JOB_TEST_PRUNER');
        self::assertResponseStatusCodeSame(422);
        self::assertCount(5, $this->remainingStatuses());
    }

    public function testReadPermissionIsNotEnough(): void
    {
        $this->post('{"days": 30}', 'ROLE_JOB_TEST_READER');
        self::assertResponseStatusCodeSame(403);
        self::assertCount(5, $this->remainingStatuses());
    }

    public function testInputSchemaResolvesThroughTheChain(): void
    {
        // The chain also feeds the OpenAPI request body (OperationInputSchemaDecorator)
        $schema = static::getContainer()->get(InputSchemaResolverInterface::class)->resolve(JobPruneProcessor::OPERATION);

        self::assertIsArray($schema);
        self::assertSame(['days', 'dryRun'], array_keys($schema['properties']));
    }

    public function testCommandMirrorsTheOperation(): void
    {
        $tester = new CommandTester((new Application(static::$kernel))->find('app:jobs:prune'));

        self::assertSame(0, $tester->execute(['--days' => '30', '--dry-run' => true]));
        self::assertStringContainsString('Would delete 2', $tester->getDisplay());
        self::assertCount(5, $this->remainingStatuses());

        self::assertSame(0, $tester->execute(['--days' => '30']));
        self::assertSame(['completed', 'pending', 'running'], $this->remainingStatuses());

        self::assertSame(2, $tester->execute(['--days' => '0']));
    }

    /**
     * @return array<string, mixed>
     */
    private function post(string $json, string $roles): array
    {
        $this->client->request('POST', '/api/admin/jobs/prune', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TEST_ROLES' => $roles,
        ], content: $json);

        return json_decode((string) $this->client->getResponse()->getContent(), true) ?? [];
    }

    /**
     * @return list<string>
     */
    private function remainingStatuses(): array
    {
        return $this->entityManager()->getConnection()
            ->fetchFirstColumn('SELECT status FROM dmstr_job ORDER BY status');
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel($options['environment'] ?? 'test', $options['debug'] ?? true);
    }
}
