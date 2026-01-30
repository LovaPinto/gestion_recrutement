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
use App\Repository\RoleRepository;

class UsersController extends AbstractController
{
    // ================= LOGIN CANDIDAT =================
#[Route('/candidat/login', name: 'candidat_login')]
public function candidatLogin(Request $request, EntityManagerInterface $em): Response
{
    // 🔹 affichage après logout
    if ($request->query->get('logout')) {
        return $this->render('candidate/login_candidate.html.twig', [
            'logoutSuccess' => true
        ]);
    }

    if ($request->isMethod('POST')) {

        $email    = trim($request->request->get('email'));
        $password = trim($request->request->get('password'));

        if (!$email || !$password) {
            return $this->render('candidate/login_candidate.html.twig', [
                'loginError' => true
            ]);
        }

        $user = $em->getRepository(Users::class)->findOneBy([
            'email' => $email
        ]);

        if (!$user || !$user->getRole() || $user->getRole()->getId() !== 3) {
            return $this->render('candidate/login_candidate.html.twig', [
                'loginError' => true
            ]);
        }

        if ($user->getPassword() !== $password) {
            return $this->render('candidate/login_candidate.html.twig', [
                'loginError' => true
            ]);
        }

        $this->startUserSession($request, $user);

        return $this->render('candidate/login_candidate.html.twig', [
            'loginSuccess' => true
        ]);
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

    // redirection avec flag logout
    return $this->redirectToRoute('candidat_login', [
        'logout' => 1
    ]);
}


    // ================= PROFIL CANDIDAT =================
 #[Route('/candidat/profil', name: 'app_candidate_profil', methods: ['GET', 'POST'])]
    public function profil(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('candidat_login');
        }

        $user = $em->getRepository(Users::class)->find($userId);
        if (!$user) {
            $session->invalidate();
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

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_candidate_profil');
        }

        return $this->render('candidate/afficherProfil.html.twig', [
            'form' => $form->createView(),
            'candidate' => $candidate,
            'user' => $user,
        ]);
    }


    // ================= SESSIONS =================
  private function startUserSession(Request $request, Users $user): void
{
    $session = $request->getSession();

    $session->set('user_id', $user->getId());
    $session->set('first_name', $user->getFirstName());
    $session->set('last_name', $user->getLastName());
    $session->set('email', $user->getEmail());
    $session->set('role_id', $user->getRole()?->getId());
    $session->set('logged_in', true);
}

    // ================= LOGIN DEPARTEMENT / RH / MANAGER =================
  #[Route('/login-department', name: 'login_department')]
public function loginDepart(
    Request $request,
    EntityManagerInterface $em
): Response
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

        $roleId   = $role->getId();              
        $roleType = strtolower($role->getType());

        if (!in_array($roleId, [1, 2])) {
            $this->addFlash('error', 'Accès refusé : rôle non autorisé.');
            return $this->redirectToRoute('login_department');
        }

        /* ================== SESSION ================== */
        $session = $request->getSession();
        $session->set('user_id', $user->getId());
        $session->set('role_id', $roleId);
        $session->set('role_type', $roleType);

        $this->addFlash('success', 'Connexion réussie.');

        /* ================== REDIRECTION ================== */
        if ($roleId === 1) {
            return $this->redirectToRoute('dashboard_manager');
        }
        if ($roleId === 2) {
            return $this->redirectToRoute('dashboardManager');
        }
    }

    return $this->render('department/formulaireLoginDepart.html.twig');
}


    // ================= AJOUT UTILISATEUR =================
#[Route('/users/ajout', name: 'users_add')]
public function add(
    Request $request,
    EntityManagerInterface $em,
    UsersRepository $usersRepo,
    RoleRepository $roleRepo
): Response {

    /* ================= FORM AJOUT ================= */
    $user = new Users();
    $form = $this->createForm(UsersType::class, $user);
    $form->handleRequest($request);

    /* ================= AJOUT RH ================= */
    if ($form->isSubmitted() && $form->isValid()) {

        $email = $form->get('email')->getData();

        // 🔴 Email déjà utilisé
        if ($usersRepo->findOneBy(['email' => $email])) {

            $this->addFlash('error', 'Ce n’est pas votre email ou il est déjà utilisé.');

            return $this->redirectToRoute('users_add');
        }

        // 🔴 Mot de passe obligatoire
        if (!$form->get('password')->getData()) {

            $this->addFlash('error', 'Le mot de passe est obligatoire.');

            return $this->redirectToRoute('users_add');
        }

        // ✅ Ajout
        $user->setPassword($form->get('password')->getData());

        // rôle RH forcé (id = 1)
        $roleRH = $roleRepo->find(1);
        $user->setRole($roleRH);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'RH ajouté avec succès.');

        // ✅ REDIRECTION OBLIGATOIRE (PRG)
        return $this->redirectToRoute('users_add');
    }

    /* ================= MODIFICATION ================= */
    if ($request->isMethod('POST') && $request->request->get('edit_id')) {

        $u = $usersRepo->find($request->request->get('edit_id'));

        if ($u) {
            $u->setFirstName($request->request->get('firstName'));
            $u->setLastName($request->request->get('lastName'));
            $u->setEmail($request->request->get('email'));

            $em->flush();

            $this->addFlash('success', 'Modification enregistrée avec succès.');
        }

        return $this->redirectToRoute('users_add');
    }

    /* ================= SUPPRESSION ================= */
    if ($request->isMethod('POST') && $request->request->get('delete_id')) {

        $u = $usersRepo->find($request->request->get('delete_id'));

        if ($u) {
            $em->remove($u);
            $em->flush();

            $this->addFlash('success', 'RH supprimé avec succès.');
        }

        return $this->redirectToRoute('users_add');
    }

    /* ================= AFFICHAGE ================= */
    return $this->render('users/formulaireAjoutUsers.html.twig', [
        'form'  => $form->createView(),
        'users' => $usersRepo->findBy(['role' => 1]),
    ]);
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
