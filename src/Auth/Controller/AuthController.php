<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\TokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenManager $tokenManager,
        private TokenStorageInterface $tokenStorage,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');


        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'status' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'message' => 'Validation failed',
                'errors' => $errorMessages,
            ], Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            return $this->json([
                'status' => 'error',
                'message' => 'User already exists'
            ], Response::HTTP_CONFLICT);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

        // Persist entities
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'userId' => $user->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $payloadUser = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        if (!$payloadUser->getEmail() || !$payloadUser->getPassword()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Email and password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $payloadUser->getEmail()]);
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $payloadUser->getPassword())) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->tokenManager->createToken($user);

        return $this->json([
            'status' => 'success',
            'userId' => $user->getId(),
            'token' => $token
        ]);
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->tokenManager->removeTokens($user);

        return $this->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }
}
