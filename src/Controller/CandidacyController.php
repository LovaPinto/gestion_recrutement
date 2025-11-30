<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CandidacyController extends AbstractController
{
    #[Route('/candidacy', name: 'app_candidacy')]
    public function index(): Response
    {
        return $this->render('candidacy/index.html.twig', [
            'controller_name' => 'CandidacyController',
        ]);
    }
}
