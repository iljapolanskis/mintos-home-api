<?php

namespace App\Auth\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    public const HEADER_API_TOKEN = 'X-API-TOKEN';

    public function __construct(
        private TokenManager $tokenManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // Always return true for API endpoints to force authentication
        // This ensures all requests to protected routes are authenticated
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(static::HEADER_API_TOKEN);
        if (!$apiToken) {
            throw new CustomUserMessageAuthenticationException('API token is missing');
        }

        return new SelfValidatingPassport(
            new UserBadge($apiToken, function ($apiToken) {
                $user = $this->tokenManager->getUserByToken($apiToken);
                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Invalid API token');
                }

                if (!$this->tokenManager->validateToken($apiToken, $user)) {
                    throw new CustomUserMessageAuthenticationException('API token has expired');
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On success, return null to continue the request
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'status' => 'error',
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
