<?php
// file generated with AI assistance: Claude Code - 2026-10-28 23:11:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional\App;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Authenticates a request by the header "X-Test-Roles: ROLE_A, ROLE_B".
 */
final class TestAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public const HEADER = 'X-Test-Roles';

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER);
    }

    public function authenticate(Request $request): Passport
    {
        $roles = array_values(array_filter(
            array_map('trim', explode(',', (string) $request->headers->get(self::HEADER))),
            static fn (string $role): bool => '' !== $role,
        ));

        return new SelfValidatingPassport(new UserBadge(
            'test-user',
            static fn (string $identifier): InMemoryUser => new InMemoryUser($identifier, null, $roles),
        ));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => 'unauthenticated'], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new JsonResponse(['error' => 'unauthenticated'], Response::HTTP_UNAUTHORIZED);
    }
}
