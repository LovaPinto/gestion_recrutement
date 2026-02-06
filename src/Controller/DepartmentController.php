<?php

namespace App\Controller;

use App\Entity\Department;
use App\Entity\Company;
use App\Entity\Users;
use App\Entity\Role;
use App\Entity\JobOffer;
use App\Form\DepartmentType;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class DepartmentController extends AbstractController
{
    /* =======================================================
     *  PAGE PRINCIPALE
     * ======================================================= */
    #[Route('/department', name: 'app_department')]
    public function index(): Response
    {
        return $this->render('department/departement.html.twig');
    }

    #[Route('/departmentSidebar', name: 'app_department_sidebar')]
    public function sidebar(): Response
    {
        return $this->render('layout/layout_frontend/sidebar.html.twig');
    }

    /* =======================================================
     *  LISTE + PAGE AJOUT DEPARTEMENT
     * ======================================================= */
    #[Route('/ajoutDepart', name: 'app_department_ajout')]
    public function ajoutDepart(
        EntityManagerInterface $em,
        Request $request
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

        $departements = $em->getRepository(Department::class)->findBy([
            'company' => $company
        ]);

        return $this->render('department/ajoutDepart.html.twig', [
            'departements' => $departements,
        ]);
    }

    /* =======================================================
     *  FORMULAIRE AJOUT DEPARTEMENT
     * ======================================================= */
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

        /* ========= DEPARTEMENT ========= */
        $department = new Department();
        $department->setCompany($company);

        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /* ========= ROLE MANAGER ========= */
            $managerRole = $em->getRepository(Role::class)->find(2);
            if (!$managerRole) {
                throw new \LogicException('Le rôle Manager est introuvable.');
            }

            /* ========= MANAGER ========= */
            $password = strtolower($department->getDepartmentName()) . '@123';

            $manager = new Users();
            $manager->setFirstName($form->get('managerFirstName')->getData());
            $manager->setLastName($form->get('managerLastName')->getData());
            $manager->setEmail($form->get('managerEmail')->getData());
            $manager->setPassword($password); // volontairement non hashé
            $manager->setRole($managerRole);
            $manager->setDepartment($department);

            /* ========= LIEN BIDIRECTIONNEL ========= */
            $department->setManager($manager);

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

    /* =======================================================
     *  PAGES MANAGER / RH
     * ======================================================= */
    #[Route('/manager', name: 'managerDepartment')]
    public function managerAccueil(): Response
    {
        return $this->render('department/manager.html.twig');
    }

    #[Route('/RH', name: 'RH_Department')]
    public function RHAccueil(): Response
    {
        return $this->render('department/rh.html.twig');
    }

    /* =======================================================
     *  SUPPRESSION DEPARTEMENT (SECURISEE)
     * ======================================================= */
    #[Route('/departement/delete/{id}', name: 'departement_delete', methods: ['POST'])]
    public function deleteDepartment(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        DepartmentRepository $repo
    ): JsonResponse {
        $companySession = $request->getSession()->get('company');

        if (!$companySession) {
            return new JsonResponse(['error' => 'Non autorisé'], 401);
        }

        $departement = $repo->find($id);

        if (
            !$departement ||
            $departement->getCompany()->getId() !== $companySession['id']
        ) {
            return new JsonResponse(['error' => 'Département introuvable'], 404);
        }

        if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $em->remove($departement);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    /* =======================================================
     *  MODIFICATION DEPARTEMENT
     * ======================================================= */
    #[Route('/departement/modifier/{id}', name: 'departement_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        int $id
    ): Response {
        $companySession = $request->getSession()->get('company');

        if (!$companySession) {
            return $this->redirectToRoute('loginCompany');
        }

        $departement = $em->getRepository(Department::class)->find($id);

        if (
            !$departement ||
            $departement->getCompany()->getId() !== $companySession['id']
        ) {
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

    /* =======================================================
     *  REDIRECTION SECURITE
     * ======================================================= */
    #[Route('/suivieCandidat', name: 'suivieCandidate_redirect')]
    public function suivieCandidatRedirect(): Response
    {
        return $this->redirectToRoute('dashboard_manager');
    }

    /* =======================================================
     *  DASHBOARD RH (FAKE DATA)
     * ======================================================= */
    #[Route('/dashboard/rh', name: 'dashboard_rh')]
    public function dashboardRH(): Response
    {
        return $this->render('department/dashboardRH.html.twig', [
            'user' => [
                'id' => 1,
                'name' => 'Alice RH',
                'role_id' => 1
            ],
            'jobOffers' => [
                ['title' => 'Développeur Symfony', 'status' => 'PUBLIÉE'],
                ['title' => 'Designer UI/UX', 'status' => 'EN ATTENTE'],
            ]
        ]);
    }

    /* =======================================================
     *  DASHBOARD MANAGER (ENTREPRISE SESSION)
     * ======================================================= */
    #[Route('/dashboard/RH', name: 'dashboard_manager')]
    public function dashboardManager(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        $companySession = $session->get('company');

        if (!$companySession) {
            return $this->redirectToRoute('loginCompany');
        }

        $company = $em->getRepository(Company::class)
            ->find($companySession['id']);

        if (!$company) {
            return $this->redirectToRoute('loginCompany');
        }

        $statuses = [
            JobOffer::STATUS_EN_ATTENTE,
            JobOffer::STATUS_PUBLIEE,
            JobOffer::STATUS_PRISE,
        ];

        $jobOffers = $em->getRepository(JobOffer::class)
            ->findBy(
                ['company' => $company, 'status' => $statuses],
                ['dateCreation' => 'DESC']
            );

        $offersByStatus = [];

        foreach ($statuses as $status) {
            $offersByStatus[$status] = $em->getRepository(JobOffer::class)
                ->count([
                    'company' => $company,
                    'status' => $status
                ]);
        }

        return $this->render('department/dashboardManager.html.twig', [
            'jobOffers'      => $jobOffers,
            'offersByStatus' => $offersByStatus,
        ]);
    }

    /* =======================================================
     *  LOGOUT
     * ======================================================= */
    #[Route('/logout/department', name: 'logout_department')]
    public function logoutDepartment(SessionInterface $session): RedirectResponse
    {
        $session->remove('role_id');
        $this->addFlash('success', 'Déconnexion réussie.');
        return $this->redirectToRoute('login_department');
    }
}
