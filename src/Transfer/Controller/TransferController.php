<?php

declare(strict_types=1);

namespace App\Transfer\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/transfer')]
class TransferController extends AbstractController
{
    #[Route('/create')]
    public function transfer(): Response
    {
        return $this->json([
            'status' => Response::$statusTexts[Response::HTTP_OK],
            'message' => 'Transfer created successfully',
        ], Response::HTTP_OK, [], []);
    }
}
