<?php

namespace App\Auth\Service;

use App\Auth\Entity\ApiToken;
use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TokenManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private TokenEncoder $tokenEncoder,
        private int $tokenLifetime = 86400,
    ) {
    }

    public function createToken(User $user): string
    {
        $token = $this->tokenEncoder->encodeId($user->getId(), bin2hex(random_bytes(32)));

        $apiToken = new ApiToken();
        $apiToken->setToken(password_hash($token, PASSWORD_DEFAULT));
        $apiToken->setUser($user);
        $apiToken->setExpiresAt(new \DateTimeImmutable('+' . $this->tokenLifetime . ' seconds'));

        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

        return $token;
    }

    public function getUserByToken(string $plainToken): ?User
    {
        $userId = $this->tokenEncoder->decodeId($plainToken);

        $user = $this->userRepository->find((int)$userId);
        if (!$user) {
            return null;
        }

        return $user;
    }

    public function validateToken(string $plainToken, ?User $user = null): bool
    {
        if ($user === null) {
            $user = $this->getUserByToken($plainToken);
        }

        if (!$user) {
            return false;
        }

        foreach ($user->getApiTokens() as $token) {
            if (($token->getExpiresAt() < new \DateTimeImmutable()) || empty($token->getToken())) {
                $this->entityManager->remove($token);
                continue;
            }
            if (password_verify($plainToken, $token->getToken())) {
                return true;
            }
        }

        return false;
    }

    public function removeTokens(User $user): void
    {
        $tokens = $user->getApiTokens();

        foreach ($tokens as $token) {
            $this->entityManager->remove($token);
        }

        $this->entityManager->flush();
    }
}
