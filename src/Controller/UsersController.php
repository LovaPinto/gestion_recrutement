<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends AbstractController
{
    #[Route('/login', name: 'app_user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('users/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout() {}

    #[Route('/authentification', name: 'app_authentification', methods: ['POST'])]
    public function authentification(Request $request, UsersRepository $UsersRepository): Response
    {
        $first_name = $request->request->get('first_name');   
        $last_name  = $request->request->get('last_name');
        $email      = $request->request->get('email');
        $password   = $request->request->get('password');

        $erreur = null;

        // Vérification des champs vides
        if (empty($email) || empty($password)) {
            $erreur = "Un ou plusieurs champs sont vides.";
            return $this->render('candidate/_form.html.twig', [
                'datas' => $erreur
            ]);
        }

        // Recherche de l'utilisateur
        $user = $UsersRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $erreur = "Email introuvable.";
            return $this->render('candidate/_form.html.twig', [
                'datas' => $erreur
            ]);
        }

        // ⚠️ Comparaison simple du mot de passe (tu as demandé sans hash)
        if ($user->getPassword() !== $password) {
            $erreur = "Mot de passe incorrect.";
            return $this->render('candidate/_form.html.twig', [
                'datas' => $erreur
            ]);
        }

        // Connexion réussie
        return $this->redirectToRoute('candidate_dashboard');
    }
}
