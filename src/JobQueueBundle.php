<?php
// file generated with AI assistance: Claude Code - 2026-06-10 11:30:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class JobQueueBundle extends AbstractBundle
{
    protected string $extensionAlias = 'dmstr_symfony_job_queue';

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import(__DIR__ . '/../config/services.yaml');
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        // Empty configuration tree for now — bundle is config-less.
    }

    public function prependExtension(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->extension('doctrine_migrations', [
            'migrations_paths' => [
                'Dmstr\\SymfonyJobQueue\\Migrations' => __DIR__ . '/../migrations',
            ],
        ]);
    }
}
