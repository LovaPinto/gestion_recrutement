<?php
namespace App\Controller;

use App\Entity\Users;
use App\Entity\Role;
use App\Entity\Candidate;
use App\Form\UsersType;
use App\Form\CandidateType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UsersController extends AbstractController
{
    // ================= LOGIN CANDIDAT =================
    #[Route('/candidat/login', name: 'candidat_login')]
    public function candidatLogin(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            if (empty($email) || empty($password)) {
                $this->addFlash('error', 'Tous les champs sont requis.');
                return $this->redirectToRoute('candidat_login');
            }

            $user = $em->getRepository(Users::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash('error', 'Email ou mot de passe incorrect.');
                return $this->redirectToRoute('candidat_login');
            }

            $roleType = strtolower($user->getRole()?->getType() ?? '');
            if (!in_array($roleType, ['candidate', 'candidat'])) {
                $this->addFlash('error', 'Accès refusé : vous n\'êtes pas un candidat.');
                return $this->redirectToRoute('candidat_login');
            }

            if ($user->getPassword() !== $password) {
                $this->addFlash('error', 'Email ou mot de passe incorrect.');
                return $this->redirectToRoute('candidat_login');
            }

            // Lancer la session et créer le candidat si nécessaire
            $this->startUserSession($request, $user, $em);

            $this->addFlash('success', 'Connexion réussie.');
            return $this->redirectToRoute('app_candidate_dashboard');
        }

        return $this->render('candidate/login_candidate.html.twig');
    }

    // ================= SIGNUP CANDIDAT =================
    #[Route('/candidat/signup', name: 'candidat_signup')]
    public function candidatSignup(Request $request, UsersRepository $usersRepository, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('first_name');
            $lastName  = $request->request->get('last_name');
            $email     = $request->request->get('email');
            $password  = $request->request->get('password');

            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $this->addFlash('error', 'Tous les champs sont requis.');
                return $this->redirectToRoute('candidat_signup');
            }

            if ($usersRepository->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('candidat_signup');
            }

            $user = new Users();
            $user->setFirstName($firstName)
                 ->setLastName($lastName)
                 ->setEmail($email)
                 ->setPassword($password);

            // Attribution du rôle Candidat
            $roleCandidat = $em->getRepository(Role::class)->find(3);
            if (!$roleCandidat) {
                throw new \Exception("Le rôle Candidat (id=3) est introuvable !");
            }
            $user->setRole($roleCandidat);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Inscription réussie, vous pouvez vous connecter.');
            return $this->redirectToRoute('candidat_login');
        }

        return $this->render('candidate/signup.html.twig');
    }

    // ================= LOGOUT =================
    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->invalidate();
        $this->addFlash('success', 'Déconnexion réussie.');
        return $this->redirectToRoute('candidat_login');
    }

    // ================= PROFIL CANDIDAT =================
    #[Route('/candidat/profil', name: 'app_candidate_profil', methods: ['GET', 'POST'])]
    public function profil(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre profil.');
            return $this->redirectToRoute('candidat_login');
        }

        $user = $em->getRepository(Users::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('candidat_login');
        }

        $candidate = $em->getRepository(Candidate::class)->findOneBy(['user' => $user]);
        if (!$candidate) {
            $candidate = new Candidate();
            $candidate->setUser($user)
                      ->setNom($user->getLastName())
                      ->setPrenom($user->getFirstName())
                      ->setEmail($user->getEmail());

            $em->persist($candidate);
            $em->flush();
        }

        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $candidate->setEmail($user->getEmail());
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('app_candidate_profil');
        }

        return $this->render('candidate/profil.html.twig', [
            'form' => $form->createView(),
            'candidate' => $candidate,
        ]);
    }

    // ================= SESSIONS =================
    private function startUserSession(Request $request, Users $user, EntityManagerInterface $em): void
    {
        $session = $request->getSession();
        $session->set('user_id', $user->getId());
        $session->set('first_name', $user->getFirstName());
        $session->set('last_name', $user->getLastName());
        $session->set('email', $user->getEmail());
        $session->set('role', strtolower($user->getRole()?->getType() ?? ''));
        $session->set('role_id', $user->getRole()?->getId()); // <-- important
        $session->set('logged_in', true);

        $roleType = strtolower($user->getRole()?->getType() ?? '');
        if (in_array($roleType, ['candidate', 'candidat'])) {
            $candidate = $em->getRepository(Candidate::class)->findOneBy(['user' => $user]);
            if (!$candidate) {
                $candidate = new Candidate();
                $candidate->setUser($user)
                          ->setNom($user->getLastName())
                          ->setPrenom($user->getFirstName())
                          ->setEmail($user->getEmail());

                $em->persist($candidate);
                $em->flush();
            }
            $session->set('candidate_id', $candidate->getId());
        }
    }

    // ================= LOGIN DEPARTEMENT / RH / MANAGER =================
   #[Route('/login-department', name: 'login_department')]
    public function loginDepart(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {

            $email    = $request->request->get('email');
            $password = $request->request->get('password');

            /* ================== USER ================== */
            $user = $em->getRepository(Users::class)->findOneBy([
                'email' => $email
            ]);

            if (!$user) {
                $this->addFlash('error', 'Utilisateur non trouvé.');
                return $this->redirectToRoute('login_department');
            }

            // ⚠️ (pour l’instant, password en clair — à améliorer plus tard)
            if ($user->getPassword() !== $password) {
                $this->addFlash('error', 'Mot de passe incorrect.');
                return $this->redirectToRoute('login_department');
            }

            /* ================== ROLE ================== */
            $role = $user->getRole();

            if (!$role) {
                $this->addFlash('error', 'Aucun rôle attribué à cet utilisateur.');
                return $this->redirectToRoute('login_department');
            }

            $roleId   = $role->getId();   // 1 = RH | 2 = Manager
            $roleType = strtolower($role->getType());

            // ❌ INTERDIT : candidat ou autre rôle
            if (!in_array($roleId, [1, 2])) {
                $this->addFlash('error', 'Accès refusé : rôle non autorisé.');
                return $this->redirectToRoute('login_department');
            }

            /* ================== SESSION ================== */
            $session = $request->getSession();
            $session->set('user_id', $user->getId());
            $session->set('role_id', $roleId);

            // (optionnel mais utile)
            $session->set('role_type', $roleType);

            $this->addFlash('success', 'Connexion réussie.');

            return $this->redirectToRoute('dashboard_user_depart');
        }

        return $this->render('department/formulaireLoginDepart.html.twig');
    }


    // ================= AJOUT UTILISATEUR =================
    #[Route('/users/ajout', name: 'users_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($form->get('password')->getData());
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur ajouté avec succès');
            return $this->redirectToRoute('users_add');
        }

        return $this->render('users/formulaireAjoutUsers.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ================= AUTRES ROUTES =================
    #[Route('/new-user', name: 'app_new_user')]
    public function newUser(): Response
    {
        return $this->render('users/form_Add_user.html.twig');
    }

    #[Route('/insert-user', name: 'app_insert_user', methods: ['POST'])]
    public function insertUser(Request $request, UsersRepository $usersRepository): Response
    {
        $firstname = $request->request->get('first_name');
        $lastname  = $request->request->get('last_name');
        $email     = $request->request->get('email');
        $password  = $request->request->get('password');

        if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)) {
            $usersRepository->saveUser($firstname, $lastname, $email, $password);
            return $this->redirectToRoute('app_user_login');
        }

        $this->addFlash('error', 'Un ou plusieurs champs sont vides.');
        return $this->redirectToRoute('app_new_user');
    }

    #[Route('/loginDepart', name: 'connexionDepart')]
    public function connexion(): Response
    {
        return $this->render('department/formulaireLoginDepart.html.twig');
    }

    #[Route('/SignUpDepart', name: 'creer_Compte_Department')]
    public function createAccountDepart(): Response
    {
        return $this->render('department/formulaireSignUpDepart.html.twig');
    }

    #[Route('/ajoutUsers', name: 'add_users')]
    public function addUsers(): Response
    {
        return $this->render('users/ajoutUsers.html.twig');
    }
}
