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

final class AdminAffichePageController extends AbstractController
{
   //endpoint admin
    #[Route('/admin/form', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/formAdminConnection.html.twig',['message' => '']);
    }


//endpoint qui affiche le dashboard de l admin
#[Route('/admin/dashboard', name: 'admin_dashboard')]
public function dashboard(CompanyRepository $companyRepository): Response {

    $menu1=[
            "h1"=>"Nombre d'offres",
            "nombre"=>"0",
            "type"=>"Offres",
            "p"=>"Total des offres crées"
    ];
    $menu2=[
            "h1"=>"Utilisateurs actifs",
            "nombre"=>"0",
            "type"=>"Offres",
            "p"=>"Total des utilisateurs actifs"
    ];
    $menu3=[
            "h1"=>"Candidats",
            "nombre"=>"0",
            "type"=>"Offres",
            "p"=>"Total des candidats postulés"
    ];
    $menu4=[
            "h1"=>"Entreprises",
            "nombre"=>$companyRepository->countCompanies(),
            "type"=>"Offres",
            "p"=>"Total des entreprises"
    ];

    $tab=[
        "menu1"=>$menu1,
        "menu2"=>$menu2,
        "menu3"=>$menu3,
        "menu4"=>$menu4
    ];
    $chartLabels = ['Jan', 'Fev', 'Mar'];
    $chartData = [65, 59, 80, 81, 56, 55, 40];


    return $this->render('admin/interfaceAdmin.html.twig' ,["title"=>"Tableau De Bord" ,"allmenu"=>$tab,  "chartLabels" => $chartLabels,
        "chartData" => $chartData]);
}




//afficher les dashbord de candidat
#[Route('/admin/candidat', name: 'admin_candidate_dashboard')]
public function candidates(NewUserRepository $userRepo): Response {
    $data1=$userRepo->countNewUser(2);
        $menu1=[
            "h1"=>"Total candidats",
            "nombre"=>"0",
            "type"=>"candidat",
            "p"=>"Toutes les candidats inscrit dans le système"
    ];
    $menu2=[
            "h1"=>"Entreprises actives",
            "nombre"=>"0",
            "type"=>"candidat",
            "p"=>"Ceux qui on postulé ou mise leur profil récemment"
    ];
    $menu3=[
            "h1"=>"Entreprises inactives",
            "nombre"=>"0",
            "type"=>"candidat",
            "p"=>"ceux qui attendent une réponse"
    ];
    $menu4=[
            "h1"=>"Nouvelles ce mois",
            "nombre"=>"9",
            "type"=>"candidat",
            "p"=>"Nouveaux candidats ce mois"
    ];

    $tab=[
        "menu1"=>$menu1,
        "menu2"=>$menu2,
        "menu3"=>$menu3,
        "menu4"=>$menu4
    ];
    return $this->render(
        'admin/sectionOffre.html.twig' ,
        ["title"=>"Candidats",
        "allmenu"=>$tab ,
        "model"=>"Candidat",
        "url"=>"/admin/form/utilisateur"
    ]);
}


//afficher les dashboard de rh
#[Route('/admin/rh', name: 'admin_rh')]
public function rh(NewUserRepository $userRepo): Response {
    $data1=$userRepo->countNewUser(2);
    $data2=$userRepo->countNewUserThisMonth(2);
    $menu1=[
            "h1"=>"Total RH","nombre"=>$data1,"type"=>"RH","p"=>"Total des RH créés"];
    $menu2=[
            "h1"=>"Nouveaux RH","nombre"=>$data2,"type"=>"RH","p"=>"Nouveaux RH ce mois"];
    $menu3=[
            "h1"=>"Demandes traitées","nombre"=>"0","type"=>"RH","p"=>"Total des demandes traitées"];
    $menu4=[
            "h1"=>"Demandes ce mois",
            "nombre"=>"0",
            "type"=>"RH",
            "p"=>"Demandes traitées ce mois"
    ];

    $tab=[
        "menu1"=>$menu1,
        "menu2"=>$menu2,
        "menu3"=>$menu3,
        "menu4"=>$menu4
    ];
    return $this->render(
        'admin/sectionRh.html.twig' ,
        ["title"=>"Rh",
        "allmenu"=>$tab ,
        "model"=>"Rh",
        "url"=>"/admin/form/utilisateur"
    ]);
}

//afficher les dashboard de manager
#[Route('/admin/manager', name: 'admin_manager')]
public function manager(NewUserRepository $userRepo): Response {
    $data1=$userRepo->countNewUser(5);
    $data2=$userRepo->countNewUserThisMonth(5);
    $menu1=[
            "h1"=>"Total Manager","nombre"=>$data1,"type"=>"Manager","p"=>"Total des Manager créés"];
    $menu2=[
            "h1"=>"Manager actif","nombre"=>$data2,"type"=>"Manager","p"=>"Manager actif dans le système"];
    $menu3=[
            "h1"=>"Manager inactif","nombre"=>"0","type"=>"Manager","p"=>"Manager inactif dans le système"];
    $menu4=[
            "h1"=>"Manager avec demandes",
            "nombre"=>"0",
            "type"=>"Manager",
            "p"=>"manager ayant créés des demandes ce mois"
    ];

    $tab=[
        "menu1"=>$menu1,
        "menu2"=>$menu2,
        "menu3"=>$menu3,
        "menu4"=>$menu4
    ];
    return $this->render(
        'admin/sectionManager.html.twig' ,
        ["title"=>"Manager",
        "allmenu"=>$tab ,
        "model"=>"Manager",
        "url"=>"/admin/form/utilisateur"
    ]);
}



//afficher les dashboard de entreprise
#[Route('/admin/entreprise', name: 'admin_entreprise')]
public function entreprise(CompanyRepository $company,NewUserRepository $newUser): Response {
    $data1=$company->countCompanies();
    //company actif
    $data2=$company->countCompanyByStatus(true);
    //company inactif
    $data3=$company->countCompanyByStatus(false);
    $menu1=[
            "h1"=>"Total Entreprise","nombre"=>$data1,"type"=>"Entreprise","p"=>"Total des Entreprise créés"];
    $menu2=[
            "h1"=>"Entreprise actif","nombre"=>$data2,"type"=>"Entreprise","p"=>"Entreprise actif dans le système"];
    $menu3=[
            "h1"=>"Entreprise inactif","nombre"=>$data3,"type"=>"Entreprise","p"=>"Entreprise inactif dans le système"];
    $menu4=[
            "h1"=>"Entreprise avec demandes",
            "nombre"=>"0",
            "type"=>"Entreprise",
            "p"=>"Entreprise ayant créés des demandes ce mois"
    ];

    $tab=[
        "menu1"=>$menu1,
        "menu2"=>$menu2,
        "menu3"=>$menu3,
        "menu4"=>$menu4
    ];
  $dataEntreprise = array();

// toutes les entreprises
foreach ($company->findAllCompany() as $allCompany) {
    $nom = $allCompany->getCompanyName();
    // découper le nom en mots
    $mots = explode(" ", $nom);

    // initialiser l'avatar
    $avatar = "";
    foreach ($mots as $mot) {
        if (!empty($mot)) {
            $avatar .= strtoupper(substr($mot, 0, 1));
        }
    }
    $entreprise = [
        "avatar"=>$avatar,
        "nom"       => $allCompany->getCompanyName(),
        "email"     => $allCompany->getEmail(),
        "manager"  => $newUser->countNewUserParCompany(5, $allCompany->getId()),
        "rh"        => $newUser->countNewUserParCompany(2, $allCompany->getId()),
        "candidate" => $newUser->countNewUserParCompany(3, $allCompany->getId())
    ];

    array_push($dataEntreprise, $entreprise);
}


    return $this->render(
        'admin/sectionEntreprise.html.twig' ,
        ["title"=>"Entreprise",
        "allmenu"=>$tab ,
        "model"=>"Entreprise",
        "url"=>"/admin/form/company",
        "dataEntreprises"=>$dataEntreprise
    ]);
}

//affiche le dashboard de l offre
#[Route('/admin/offre', name: 'admin_offre')]
public function offre(NewUserRepository $userRepo): Response {
    $data1=$userRepo->countNewUser(1);
    $menu1=[
            "h1"=>"En attente","nombre"=>$data1,"type"=>"offres","p"=>"offres en attente"];
    $menu2=[
            "h1"=>"Validées","nombre"=>'0',"type"=>"offres","p"=>"offres validés"];
    $menu3=[
            "h1"=>"Publiées","nombre"=>"0","type"=>"offres","p"=>"offres publiées"];
    $menu4=[
            "h1"=>"Refusées",
            "nombre"=>"0",
            "type"=>"offres",
            "p"=>"offres refusées"
    ];

    $tab=[
        "menu1"=>$menu1,
        "menu2"=>$menu2,
        "menu3"=>$menu3,
        "menu4"=>$menu4
    ];
    return $this->render(
        'admin/sectionOffre.html.twig' ,
        ["title"=>"Entreprise",
        "allmenu"=>$tab ,
        "model"=>"Entreprise",
        "url"=>"/admin/form/company"
    ]);
}



//afficher le formulaire pour insert
//formulaire candidat
#[Route('/admin/form/utilisateur', name: 'admin_utilisaateur')]
public function candidatesform(): Response {
       $form = $this->createForm(NewUserFormType::class, null,[
            'action' =>$this->generateUrl('app_newusers_traitement'),
            'method' => 'POST',
            ]);
    return $this->render('admin/formAddUser.html.twig',
    ["model"=>"candidate",'form' => $form->createView(),'erreurinsertion'=>'coucou']);
}


//formulaire insertion entreprise
#[Route('/admin/form/company', name: 'admin_company')]
public function entrepriseform(): Response {
    return $this->render('admin/formInsertEntreprise.html.twig');
}

}
