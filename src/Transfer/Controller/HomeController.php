<?php

declare(strict_types=1);

namespace App\Transfer\Controller;

use App\Transfer\Repository\AccountRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private AccountRepository $accountRepository,
    ) {
    }

    #[Route('/')]
    public function index(): Response
    {
        $response = new Response();

        $data = $this->accountRepository->findAll();

        $response->setContent(json_encode($data));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }
}
