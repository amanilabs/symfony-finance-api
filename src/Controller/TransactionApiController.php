<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionApiController extends AbstractController
{
    #[Route('/transaction/api', name: 'app_transaction_api')]
    public function index(): Response
    {
        return $this->render('transaction_api/index.html.twig', [
            'controller_name' => 'TransactionApiController',
        ]);
    }
}
