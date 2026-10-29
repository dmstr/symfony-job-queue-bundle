<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:11:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional\App;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Collects the "permission" attribute of all services tagged
 * "za7_core.permission" into the parameter "test.registered_permissions".
 */
final class RegisteredPermissionsPass implements CompilerPassInterface
{
    public const TAG = 'za7_core.permission';

    public const PARAMETER = 'test.registered_permissions';

    public function process(ContainerBuilder $container): void
    {
        $permissions = [];
        foreach ($container->findTaggedServiceIds(self::TAG) as $tags) {
            foreach ($tags as $attributes) {
                if (isset($attributes['permission']) && \is_string($attributes['permission'])) {
                    $permissions[$attributes['permission']] = true;
                }
            }
        }

        $container->setParameter(self::PARAMETER, array_keys($permissions));
    }
}
