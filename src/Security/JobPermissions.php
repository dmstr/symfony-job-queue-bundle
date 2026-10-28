<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:13:45 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Security;

/**
 * Carries the container tag "za7_core.permission" for the permission this
 * bundle checks; the voter itself lives in za7-core.
 *
 * "job:read" is used twice, in two separate worlds that merely share the
 * string: as permission here, and as serializer normalization group of the
 * Job resource.
 */
final class JobPermissions
{
    public const string READ = 'job:read';
}
