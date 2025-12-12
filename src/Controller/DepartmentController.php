<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
