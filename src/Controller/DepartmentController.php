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

        $company = $em->getRepository(\App\Entity\Company::class)
                      ->find($companySession['id']);

        if (!$company) {
            throw $this->createNotFoundException('Entreprise introuvable');
        }

        $departement = new Department();
        $departement->setCompany($company);
        $form = $this->createForm(DepartmentType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($departement);
            $em->flush();
            $this->addFlash('success', 'Département créé avec succès !');
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

    #[Route('/suivieCandidat', name: 'suivieCandidate')]
    public function suivieCandidat(): Response
    {
        return $this->render('department/suivieCandidature.html.twig', [
            'controller_name' => 'DepartmentController',
        ]);
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

    // ================= DASHBOARD MANAGER (role_id = 2) =================
#[Route('/dashboard/manager', name: 'dashboard_manager')]
public function dashboardManager(SessionInterface $session, EntityManagerInterface $em): Response
{
    // Vérification de la session company
    $companySession = $session->get('company');
    if (!$companySession) {
        $this->addFlash('error', 'Veuillez vous connecter.');
        return $this->redirectToRoute('loginCompany');
    }

    // Récupérer l'objet Company depuis l'ID de session
    $company = $em->getRepository(\App\Entity\Company::class)
                  ->find($companySession['id']);
    if (!$company) {
        $this->addFlash('error', 'Entreprise introuvable.');
        return $this->redirectToRoute('loginCompany');
    }

    // Récupérer le rôle connecté
    $roleId = $session->get('role_id'); // 1 = RH | 2 = Manager

    // Récupérer les offres selon le rôle connecté
    $jobOffers = $em->getRepository(\App\Entity\JobOffer::class)
        ->findBy([
            'company' => $company,
            'roleId'  => $roleId
        ], ['dateCreation' => 'DESC']);

    // Compter les offres par statut
    $statuses = ['en attente', 'publiee', 'prise'];
    $offersByStatus = [];

    foreach ($statuses as $status) {
        $offersByStatus[$status] = $em->getRepository(\App\Entity\JobOffer::class)
            ->count([
                'company' => $company,
                'roleId'  => $roleId,
                'status'  => $status
            ]);
    }

    return $this->render('department/dashboardManager.html.twig', [
        'company'        => $company,
        'jobOffers'      => $jobOffers,
        'roleId'         => $roleId,
        'offersByStatus' => $offersByStatus
    ]);
}


}



