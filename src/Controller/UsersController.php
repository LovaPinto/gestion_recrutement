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
    #[Route('/new-user', name: 'app_new_user')]
    public function newUser(): Response
    {
        return $this->render('users/form_Add_user.html.twig');
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout() {}
    ## Fonxtion d'Authentification
    #[Route('/authentification', name: 'app_authentification', methods: ['POST'])]
    public function authentification(Request $request, UsersRepository $UsersRepository): Response
    {
        $firstname = $request->request->get('first_name');
        $lastname  = $request->request->get('last_name');
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

        // Comparaison simple du mot de passe 
        if ($user->getPassword() !== $password) {
            $erreur = "Mot de passe incorrect.";
            return $this->render('candidate/_form.html.twig', [
                'datas' => $erreur
            ]);
        }

        // Connexion réussie
        return $this->redirectToRoute('candidate_dashboard');
    }

    ## fonction insertion d'user 


    #[Route('/insert-user', name: 'app_insert_user', methods: ['POST'])]
    public function insertUser(
        Request $request,
        UsersRepository $usersRepository
    ): Response {

        $firstname = $request->request->get('first_name');
        $lastname = $request->request->get('last_name');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $erreur = null;

        if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)) {
 
            $usersRepository->saveUser($firstname, $lastname, $email, $password);

            return $this->redirectToRoute('app_user_login');
        }

        // Si erreur
        $erreur = "Un ou plusieurs champs sont vides";

        return $this->render('candidate/form_user.html.twig', [
            'error' => $erreur
        ]);
    }

    #[Route('/loginDepart' ,name:'connexionDepart')]
    public function connexion() :Response
    {
    return $this->render('department/formulaireLoginDepart.html.twig', [
            'controller_name' => 'UsersController',]);
    }

     #[Route('/SignUpDepart', name: 'creer_Compte_Department')]
    public function createAccountDepart(): Response
    {
        return $this->render('department/formulaireSignUpDepart.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }
}
