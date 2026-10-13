<?php

namespace App\Controller;

use App\Form\NewUserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RhController extends AbstractController
{
    #[Route('/rh', name: 'app_rh')]
    public function indexrh(): Response
    {
         $form = $this->createForm(NewUserFormType::class, null,[
            'action' => $this->generateUrl('/'),
            'method' => 'POST',
            ]);
        return $this->render('templates\admin\formNewUser.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
