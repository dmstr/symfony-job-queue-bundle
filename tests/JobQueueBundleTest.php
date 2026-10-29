<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:10:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests;

use Dmstr\SymfonyJobQueue\JobQueueBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

#[CoversClass(JobQueueBundle::class)]
final class JobQueueBundleTest extends TestCase
{
    public function testPrependSkipsDoctrineMigrationsWhenExtensionIsMissing(): void
    {
        $builder = $this->createBuilder();

        (new JobQueueBundle())->getContainerExtension()->prepend($builder);

        self::assertSame([], $builder->getExtensionConfig('doctrine_migrations'));
    }

    public function testPrependRegistersMigrationsPathWhenExtensionIsPresent(): void
    {
        $builder = $this->createBuilder();
        $builder->registerExtension(new class extends Extension {
            public function getAlias(): string
            {
                return 'doctrine_migrations';
            }

            public function load(array $configs, ContainerBuilder $container): void
            {
            }
        });

        (new JobQueueBundle())->getContainerExtension()->prepend($builder);

        $config = $builder->getExtensionConfig('doctrine_migrations');
        self::assertCount(1, $config);
        self::assertArrayHasKey(
            'Dmstr\\SymfonyJobQueue\\Migrations',
            $config[0]['migrations_paths'],
        );
    }

    private function createBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('kernel.environment', 'test');
        $builder->setParameter('kernel.build_dir', sys_get_temp_dir());

        return $builder;
    }
}
