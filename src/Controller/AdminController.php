<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin/form', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/formAdminConnection.html.twig');
    }
    #[Route('/admin/form/submit', name: 'app_admin_submit',methods: ['POST'])]
    public function indexAdmin(Request $request): Response
    {
         $email = $request->request->get('email');
         $mdp = $request->request->get('mdp');
         if($email=='lovapinto@gmail.com' && $mdp=="123"){
             return $this->render('admin/formAdminConnection.html.twig');
         }
          return $this->render('admin/formAdminConnection.html.twig', [
        'message' => 'Identifiants invalides',
    ]);
    }


    #[Route('/admin/dashboard', name: 'admin_dashboard')]
public function dashboard(): Response {
    return $this->render('admin/interfaceAdmin.html.twig');
}

#[Route('/admin/companies', name: 'admin_companies')]
public function companies(): Response {
    return $this->render('admin/formInsertEntreprise.html.twig');
}

#[Route('/admin/candidates', name: 'admin_candidates')]
public function candidates(): Response {
    return $this->render('admin/candidates.html.twig');
}

}
