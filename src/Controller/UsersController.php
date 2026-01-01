<?php
namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UsersController extends AbstractController
{
    #[Route('/login', name: 'app_user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('users/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
    #[Route('/new-user', name: 'app_new_user')]
    public function newUser(): Response
    {
        return $this->render('users/form_Add_user.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {}
    ## Fonxtion d'Authentification
    #[Route('/authentification', name: 'app_authentification', methods: ['POST'])]
    public function authentification(Request $request, UsersRepository $UsersRepository): Response
    {

        $email    = $request->request->get('email');
        $password = $request->request->get('password');

        $erreur = null;

        if (empty($email) || empty($password)) {
            $erreur = "Un ou plusieurs champs sont vides.";
            return $this->render('candidate/_form.html.twig', [
                'datas' => $erreur,
            ]);
        }

        $user = $UsersRepository->findOneBy(['email' => $email]);

        if (! $user) {
            $erreur = "Email introuvable.";
            return $this->render('candidate/_form.html.twig', [
                'datas' => $erreur,
            ]);
        }

        if ($user->getPassword() !== $password) {
            $erreur = "Mot de passe incorrect.";
            return $this->render('candidate/_form.html.twig', [
                'datas' => $erreur,
            ]);
        }

        return $this->redirectToRoute('candidate_dashboard');
    }

    #[Route('/insert-user', name: 'app_insert_user', methods: ['POST'])]
    public function insertUser(
        Request $request,
        UsersRepository $usersRepository
    ): Response {

        $firstname = $request->request->get('first_name');
        $lastname  = $request->request->get('last_name');
        $email     = $request->request->get('email');
        $password  = $request->request->get('password');

        $erreur = null;

        if (! empty($firstname) && ! empty($lastname) && ! empty($email) && ! empty($password)) {

            $usersRepository->saveUser($firstname, $lastname, $email, $password);

            return $this->redirectToRoute('app_user_login');
        }

        $erreur = "Un ou plusieurs champs sont vides";

        return $this->render('candidate/form_user.html.twig', [
            'error' => $erreur,
        ]);
    }

    #[Route('/loginDepart', name: 'connexionDepart')]
    public function connexion(): Response
    {
        return $this->render('department/formulaireLoginDepart.html.twig', [
            'controller_name' => 'UsersController']);
    }

    #[Route('/SignUpDepart', name: 'creer_Compte_Department')]
    public function createAccountDepart(): Response
    {
        return $this->render('department/formulaireSignUpDepart.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }
    #[Route('/ajoutUsers', name: 'add_users')]
    public function addUsers(): Response
    {
        return $this->render('users/ajoutUsers.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }

    #[Route('/users/ajout', name: 'users_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em): Response {
        $user = new Users();

        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $form->get('password')->getData()
            );

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur ajouté avec succès');

            return $this->redirectToRoute('users_add');
        }
        return $this->render('users/formulaireAjoutUsers.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login-department', name: 'login_department')]
    public function loginDepart(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email    = $request->request->get('email');
            $password = $request->request->get('password');

            $user = $em->getRepository(Users::class)->findOneBy(['email' => $email]);

            if ($user && $user->getPassword() === $password) {

                $session = $request->getSession();
                $session->set('user_id', $user->getId());
                $session->set('email', $user->getEmail());
                $session->set('role', $user->getRole()->getType());

                if ($user->getRole()->getType() === 'RH') {
                    return $this->redirectToRoute('RH_Department');
                } elseif ($user->getRole()->getType() === 'Manager') {
                    return $this->redirectToRoute('managerDepartment');
                } else {
                    return $this->redirectToRoute('app_user_login');
                }
            } else {
                $this->addFlash('error', 'Email ou mot de passe incorrect');
            }
        }

        return $this->render('security/login_department.html.twig');
    }
}
