<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\NewUser;
use App\Form\NewUserFormType;
use App\Repository\CompanyRepository;
use App\Repository\NewUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{

 //endpoint pour le traitement de formulaire de login
    #[Route('/admin/login', name: 'app_admin_submit', methods: ['POST'])]
    public function indexAdmin(Request $request): Response
    {
         $email = $request->request->get('email');
         $mdp = $request->request->get('password');
         if($email=='lovapinto@gmail.com' && $mdp=="123"){
            return $this->render('admin/formAdminConnection.html.twig');
         }
          return $this->render('admin/formAdminConnection.html.twig', [
        'message' => 'Identifiants invalides',
    ]);
    }

//traitement du formulaire de company
#[Route('/admin/form/manager/traitment', name: 'admin_form_manager_traitement',methods: ['POST'])]
public function companyFormTraitement(Request $request,CompanyRepository $companyRepository): Response {
$companyName=$request->request->get('companyName');
$email=$request->request->get('email');
$password=$request->request->get('password');
$role_id=$request->request->get('role_id');
$descritpion=$request->request->get('description');

if(!empty($companyName) && !empty($email) && !empty($password) && !empty($role_id) &&!empty($descritpion)){
    $companyRepository->insertCompany($companyName,$email,$password,$descritpion,$role_id);
    return $this->redirectToRoute('admin_dashboard');
}
    return $this->redirectToRoute('admin_companies');
}

//traitement ALL NewUsers
#[Route('/users/form', name: 'app_newusers_traitement', methods:['POST'])]
public function indexform(Request $request, NewUserRepository $newUserRepository): Response
{
    $newuser = new NewUser();
    $form = $this->createForm(NewUserFormType::class, $newuser);
    $form->handleRequest($request);
    $erreurinsertion=null;

    if(isset($newuser)){
        if ($form->isSubmitted() && $form->isValid()) {
            if($newUserRepository->insertNewUser($newuser)){
                $erreurinsertion="utilisateur ajouter avec succes";
            }
        }
    }else{
         $erreurinsertion="aucune utilisateur ajouter";
    }
    return $this->render('admin/formAddUser.html.twig',
    ["model"=>"candidate",'form' => $form->createView(),'erreurinsertion'=>$erreurinsertion]);
    }
}
