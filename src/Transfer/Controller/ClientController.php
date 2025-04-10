<?php

declare(strict_types=1);

namespace App\Transfer\Controller;

use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use App\Transfer\Entity\Client;
use App\Transfer\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/client')]
class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientRepository $clientRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/list', name: 'client_list', methods: ['GET'])]
    public function list(): Response
    {
        $clients = $this->clientRepository->findAll();

        if (empty($clients)) {
            $clients = [
                'status' => Response::$statusTexts[Response::HTTP_NO_CONTENT],
                'message' => 'No clients found',
            ];
        }

        return $this->json($clients, Response::HTTP_OK, [], []);
    }

    #[Route('/self', name: 'client_self', methods: ['GET'])]
    public function self(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $client = $user?->getClient();

        if (!$client) {
            return $this->json([
                'status' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'message' => 'Client not found',
            ], Response::HTTP_NOT_FOUND, [], []);
        }

        return $this->json($client, Response::HTTP_OK, [], []);
    }

    #[Route('/{id}', name: 'client_get', methods: ['GET'])]
    public function show(int $id): Response
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return $this->json([
                'status' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'message' => 'Client not found',
            ], Response::HTTP_NOT_FOUND, [], []);
        }

        return $this->json($client, Response::HTTP_OK, [], []);
    }


    #[Route('', name: 'client_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            if ($request->getContentTypeFormat() !== 'json') {
                throw new BadRequestException('Unsupported content format');
            }

            /** @var ?User $user */
            $user = $this->getUser();
            $existingClient = $user?->getClient();
            if ($existingClient) {
                return $this->json([
                    'status' => Response::$statusTexts[Response::HTTP_CONFLICT],
                    'message' => 'Client already exists',
                ], Response::HTTP_CONFLICT);
            }

            $client = $this->serializer->deserialize($request->getContent(), Client::class, 'json');
            $client->setUser($user);

            $errors = $this->validator->validate($client);
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

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            return $this->json($client, Response::HTTP_CREATED, [], []);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                'message' => 'Failed to create client: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'client_update', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        try {
            $client = $this->clientRepository->find($id);

            if (!$client) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Client not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['firstName'])) {
                $client->setFirstName($data['firstName']);
            }

            if (isset($data['lastName'])) {
                $client->setLastName($data['lastName']);
            }

            if (isset($data['email'])) {
                $client->setEmail($data['email']);
            }

            $errors = $this->validator->validate($client);
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

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            return $this->json($client, Response::HTTP_OK, [], []);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                'message' => 'Failed to update client: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'client_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        try {
            $client = $this->clientRepository->find($id);

            if (!$client) {
                return $this->json([
                    'status' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'message' => 'Client not found',
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if client has accounts
            if (!$client->getAccounts()->isEmpty()) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Cannot delete client with active accounts',
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->remove($client);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Client deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to delete client: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
