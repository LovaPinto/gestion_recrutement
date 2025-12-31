<?php

namespace App\Controller;
use App\Entity\Department;
use App\Form\DepartmentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;


final class DepartmentController extends AbstractController
{
    #[Route('/department', name: 'app_department')]
    public function index(): Response
    {
        return $this->render('department/index.html.twig', [
            'controller_name' => 'DepartmentController',
        ]);
    }

    #[Route('/departmentSidebar',name:'app_department_sidebar')]
    public function sidebar():Response
    {
        return $this->render('layout/layout_frontend/sidebar.html.twig',[
            'controller_name'=>'DepartmentController'
        ]);
    }

     #[Route('/ajoutDepart' ,name:'app_department_ajout')]
    public function ajoutDepart(EntityManagerInterface $em):Response
    {
        $departements = $em->getRepository(Department::class)->findAll();
        return $this->render('department/ajoutDepart.html.twig',[
            'departements' => $departements,
            'controller_name' =>'DepartmentController'
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

    #[Route ('/manager', name :'managerDepartment')]
    public function managerAccueil(): Response
    {
        return $this->render('department/manager.html.twig',[
            'controller_name'=>'DepartmentController'
        ]);
    }
}