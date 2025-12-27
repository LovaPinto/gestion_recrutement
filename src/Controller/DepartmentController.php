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
    public function ajoutDepart(EntityManagerInterface $em): Response
    {
        $departements = $em->getRepository(Department::class)->findAll();
        return $this->render('department/ajoutDepart.html.twig', [
            'departements'    => $departements,
            'controller_name' => 'DepartmentController',
        ]);
    }
    #[Route('/formulaireAjoutDepart', name: 'formulaire_departement')]
    public function formulaireAjoutDepart(Request $request, EntityManagerInterface $em): Response
    {

        $departement = new Department();

        $form = $this->createForm(DepartmentType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($departement);
            $em->flush();

            $this->addFlash('success', 'Département créé avec succès !');
            return $this->redirectToRoute('formulaire_departement');
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

        if (! $departement) {
            return new JsonResponse(['error' => 'Département non trouvé'], 404);
        }

        $token = $request->request->get('_token');

        if (! $this->isCsrfTokenValid('delete' . $id, $token)) {
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

        if (! $departement) {
            throw $this->createNotFoundException('Département introuvable');
        }

        $form = $this->createForm(DepartmentType::class, $departement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            return $this->redirectToRoute('app_department_ajout');
        }

        return $this->render('department/formulaireModifDepart.html.twig', [
            'form'        => $form->createView(),
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

}
