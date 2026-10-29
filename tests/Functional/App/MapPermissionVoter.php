<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:11:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional\App;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * Grants a registered permission when one of the user's roles is mapped to it.
 */
final class MapPermissionVoter extends Voter
{
    /**
     * @param list<string>                $registered permissions found by RegisteredPermissionsPass
     * @param array<string, list<string>> $map        permission => role names
     */
    public function __construct(
        private readonly array $registered,
        private readonly array $map,
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return \in_array($attribute, $this->registered, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (null === $token->getUser()) {
            return false;
        }

        return [] !== array_intersect($token->getRoleNames(), $this->map[$attribute] ?? []);
    }
}
