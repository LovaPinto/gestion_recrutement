<?php
namespace App\Controller;

use App\Entity\Department;
use App\Form\DepartmentType;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\JobOfferRepository;
use App\Repository\CandidacyRepository;
use App\Service\CvTextExtractor;
use App\Service\AtsAiService;
use App\Entity\Candidacy;
use App\Entity\JobOffer;
use App\Entity\Users;
use App\Entity\Company;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Role;
final class DepartmentController extends AbstractController
{
    #[Route('/department', name: 'app_department')]
    public function index(): Response
    {
        return $this->render('department/departement.html.twig', [
            'controller_name' => 'DepartmentController',
        ]);
    }

    #[Route('/departmentSidebar', name: 'app_department_sidebar')]
    public function sidebar(): Response
    {
        return $this->render('layout/layout_frontend/sidebar.html.twig', [
            'controller_name' => 'DepartmentController',
        ]);
    }

    #[Route('/ajoutDepart', name: 'app_department_ajout')]
    public function ajoutDepart(
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        $company = $request->getSession()->get('company');
        if (!$company) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('loginCompany');
        }

        $companyId = $company['id'];
        $departements = $em->getRepository(Department::class)->findBy([
            'company' => $companyId
        ]);

        return $this->render('department/ajoutDepart.html.twig', [
            'departements' => $departements,
        ]);
    }

#[Route('/formulaireAjoutDepart', name: 'formulaire_departement')]
public function formulaireAjoutDepart(
    Request $request,
    EntityManagerInterface $em
): Response {

    $companySession = $request->getSession()->get('company');
    if (!$companySession) {
        $this->addFlash('error', 'Veuillez vous connecter.');
        return $this->redirectToRoute('loginCompany');
    }

    $company = $em->getRepository(Company::class)
        ->find($companySession['id']);

    if (!$company) {
        throw $this->createNotFoundException('Entreprise introuvable');
    }

    // 1️⃣ Département
    $department = new Department();
    $department->setCompany($company);

    $form = $this->createForm(DepartmentType::class, $department);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // 2️⃣ Récupération rôle MANAGER (id = 1)
        $managerRole = $em->getRepository(Role::class)->find(2);
        if (!$managerRole) {
            throw new \LogicException('Le rôle Manager  est introuvable.');
        }

        // 3️⃣ Création Manager
        $password = strtolower($department->getDepartmentName()) . '@123';

        $manager = new Users();
        $manager->setFirstName($form->get('managerFirstName')->getData());
        $manager->setLastName($form->get('managerLastName')->getData());
        $manager->setEmail($form->get('managerEmail')->getData());
        $manager->setPassword($password); // ❌ volontairement non hashé
        $manager->setRole($managerRole);
        $manager->setDepartment($department);

        // 4️⃣ LIAISON BIDIRECTIONNELLE OBLIGATOIRE
        $department->setManager($manager);

        // 5️⃣ Persist
        $em->persist($department);
        $em->persist($manager);
        $em->flush();

        $this->addFlash(
            'success',
            "Département créé avec succès. 
             Manager : {$manager->getFirstName()} {$manager->getLastName()} 
             | Mot de passe : $password"
        );

        return $this->redirectToRoute('app_department_ajout');
    }

    return $this->render('department/formulaireAjoutDepart.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/manager', name: 'managerDepartment')]
    public function managerAccueil(): Response
    {
        return $this->render('department/manager.html.twig', [
            'controller_name' => 'DepartmentController',
        ]);
    }

    #[Route('/RH', name: 'RH_Department')]
    public function RHAccueil(): Response
    {
        return $this->render('department/rh.html.twig', [
            'controller_name' => 'DepartmentController',
        ]);
    }

    #[Route('/departement/delete/{id}', name: 'departement_delete', methods: ['POST'])]
    public function deleteDepartment(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        DepartmentRepository $repo
    ): JsonResponse {

        $departement = $repo->find($id);
        if (!$departement) {
            return new JsonResponse(['error' => 'Département non trouvé'], 404);
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $id, $token)) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $em->remove($departement);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/departement/modifier/{id}', name: 'departement_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        int $id
    ): Response {

        $departement = $em->getRepository(Department::class)->find($id);
        if (!$departement) {
            throw $this->createNotFoundException('Département introuvable');
        }

        $form = $this->createForm(DepartmentType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_department_ajout');
        }

        return $this->render('department/formulaireModifDepart.html.twig', [
            'form' => $form->createView(),
            'departement' => $departement,
        ]);
    }

 
  
    // 🔒 Route de sécurité (évite le 404)
    #[Route('/suivieCandidat', name: 'suivieCandidate_redirect')]
    public function suivieCandidatRedirect(): Response
    {
        return $this->redirectToRoute('dashboard_manager');
    }

    // ================= DASHBOARD RH (role_id = 1) =================
    #[Route('/dashboard/rh', name: 'dashboard_rh')]
    public function dashboardRH(SessionInterface $session): Response
    {
        $user = [
            'id' => 1,
            'name' => 'Alice RH',
            'role_id' => 1 // corrigé ici
        ];

        $jobOffers = [
            ['id'=>1, 'title'=>'Développeur Symfony', 'status'=>'PUBLIÉE', 'department'=>'IT', 'deadline'=>'2026-02-28'],
            ['id'=>2, 'title'=>'Designer UI/UX', 'status'=>'EN ATTENTE', 'department'=>'Design', 'deadline'=>'2026-03-15'],
            ['id'=>3, 'title'=>'Chef de projet', 'status'=>'PRISE', 'department'=>'Management', 'deadline'=>'2026-03-30']
        ];

        return $this->render('department/dashboardRH.html.twig', [
            'user' => $user,
            'jobOffers' => $jobOffers
        ]);
    }

    // ================= DASHBOARD MANAGER  =================
#[Route('/dashboard/RH', name: 'dashboard_manager')]
public function dashboardManager(
    SessionInterface $session,
    EntityManagerInterface $em
): Response
{
    /* ================== ENTREPRISE EN SESSION ================== */
    $companySession = $session->get('company');

    if (!$companySession) {
        $this->addFlash('error', 'Veuillez vous connecter.');
        return $this->redirectToRoute('loginCompany');
    }

    $company = $em->getRepository(Company::class)
        ->find($companySession['id']);

    if (!$company) {
        $this->addFlash('error', 'Entreprise introuvable.');
        return $this->redirectToRoute('loginCompany');
    }

    /* ================== STATUTS AUTORISÉS ================== */
    $allowedStatuses = [
        JobOffer::STATUS_EN_ATTENTE,
        JobOffer::STATUS_PUBLIEE,
        JobOffer::STATUS_PRISE,
    ];

    /* ================== OFFRES ================== */
    $jobOffers = $em->getRepository(JobOffer::class)
        ->createQueryBuilder('j')
        ->andWhere('j.company = :company')
        ->andWhere('j.status IN (:statuses)')
        ->setParameter('company', $company)
        ->setParameter('statuses', $allowedStatuses)
        ->orderBy('j.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();

    /* ================== COMPTEURS ================== */
    $offersByStatus = [];

    foreach ($allowedStatuses as $status) {
        $offersByStatus[$status] = (int) $em
            ->getRepository(JobOffer::class)
            ->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->andWhere('j.company = :company')
            ->andWhere('j.status = :status')
            ->setParameter('company', $company)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    return $this->render('department/dashboardManager.html.twig', [
        'jobOffers'      => $jobOffers,
        'offersByStatus' => $offersByStatus,
    ]);
}



#[Route('/logout/department', name: 'logout_department')]
public function logoutDepartment(SessionInterface $session): RedirectResponse
{
    $session->remove('role_id');
    $this->addFlash('success', 'Déconnexion du département réussie.');
    return $this->redirectToRoute('login_department');
}


 }

