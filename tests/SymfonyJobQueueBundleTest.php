<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:10:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests;

use Dmstr\SymfonyJobQueue\SymfonyJobQueueBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

#[CoversClass(SymfonyJobQueueBundle::class)]
final class SymfonyJobQueueBundleTest extends TestCase
{
    public function testPrependSkipsDoctrineMigrationsWhenExtensionIsMissing(): void
    {
        $builder = $this->createBuilder();

        (new SymfonyJobQueueBundle())->getContainerExtension()->prepend($builder);

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

        (new SymfonyJobQueueBundle())->getContainerExtension()->prepend($builder);

        $config = $builder->getExtensionConfig('doctrine_migrations');
        self::assertCount(1, $config);
        self::assertArrayHasKey(
            'Dmstr\\SymfonyJobQueue\\Migrations',
            $config[0]['migrations_paths'],
        );
    }

    /**
     * Symfony Flex registers a bundle without recipe only under the class name
     * it derives from the PSR-4 namespace: last segment plus "Bundle". Any
     * other name and a new app never gets the bundle in bundles.php.
     */
    public function testClassNameMatchesTheFlexAutoRecipeGuess(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__.'/../composer.json'), true, flags: \JSON_THROW_ON_ERROR);
        $namespace = rtrim((string) array_search('src/', $composer['autoload']['psr-4'], true), '\\');
        $segments = explode('\\', $namespace);

        self::assertSame($namespace.'\\'.end($segments).'Bundle', SymfonyJobQueueBundle::class);
    }

    private function createBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('kernel.environment', 'test');
        $builder->setParameter('kernel.build_dir', sys_get_temp_dir());

        return $builder;
    }
}
// - revised 2026-10-29 (class renamed for the Flex auto-recipe)
