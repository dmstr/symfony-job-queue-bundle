<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:11:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional\App;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Dmstr\OpenApiJsonSchema\OpenApiJsonSchemaBundle;
use Dmstr\OpenApiJsonSchema\Service\OperationInputSchemaResolver;
use Dmstr\SymfonyJobQueue\SymfonyJobQueueBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Minimal application around the bundle: API Platform, Doctrine on a SQLite
 * file, header based test authentication and a map based permission voter.
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public const DEFAULT_PERMISSION_MAP = ['job:read' => ['ROLE_JOB_TEST_READER']];

    /**
     * @param array<string, list<string>> $permissionMap permission => role names that grant it
     */
    public function __construct(
        string $environment,
        bool $debug,
        private readonly array $permissionMap = self::DEFAULT_PERMISSION_MAP,
    ) {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SecurityBundle();
        yield new DoctrineBundle();
        yield new DoctrineMigrationsBundle();
        yield new ApiPlatformBundle();
        yield new OpenApiJsonSchemaBundle();
        yield new SymfonyJobQueueBundle();
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 3);
    }

    public function getCacheDir(): string
    {
        return $this->getTempDir() . '/cache';
    }

    public function getLogDir(): string
    {
        return $this->getTempDir() . '/log';
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisteredPermissionsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $container->extension('framework', [
            'test' => true,
            'secret' => 'test',
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
            'validation' => ['enabled' => true],
            'messenger' => ['default_bus' => 'messenger.bus.default', 'buses' => ['messenger.bus.default' => []]],
        ]);
        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'path' => '%kernel.cache_dir%/test.sqlite',
            ],
            'orm' => [
                'enable_native_lazy_objects' => true,
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'mappings' => [
                    'JobQueue' => [
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/src/Entity',
                        'prefix' => 'Dmstr\\SymfonyJobQueue\\Entity',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
        $container->extension('api_platform', [
            'mapping' => ['paths' => ['%kernel.project_dir%/src/Entity']],
            'enable_swagger_ui' => false,
            'enable_re_doc' => false,
            'formats' => ['jsonld' => ['application/ld+json'], 'json' => ['application/json']],
        ]);
        $container->extension('security', [
            'providers' => ['users' => ['memory' => ['users' => []]]],
            'firewalls' => [
                'main' => [
                    'stateless' => true,
                    'provider' => 'users',
                    'custom_authenticators' => [TestAuthenticator::class],
                    'entry_point' => TestAuthenticator::class,
                ],
            ],
        ]);

        $container->parameters()->set('test.permission_map', $this->permissionMap);

        $services = $container->services();
        // Without Monolog the framework logger writes to stderr and floods the test output.
        $services->set('logger', NullLogger::class);
        $services->set(TestAuthenticator::class);
        $services->set(OperationInputSchemaResolver::class)
            ->args(['$schemaBasePaths' => []]);
        $services->set(MapPermissionVoter::class)
            ->args([
                '$registered' => '%test.registered_permissions%',
                '$map' => '%test.permission_map%',
            ])
            ->tag('security.voter');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('.', 'api_platform')->prefix('/api');
    }

    private function getTempDir(): string
    {
        $hash = substr(sha1(serialize($this->permissionMap)), 0, 12);

        return sys_get_temp_dir() . '/symfony-job-queue-bundle-test/' . $this->environment . '-' . $hash;
    }
}
