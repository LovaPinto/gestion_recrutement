<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Repository\JobOfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;


final class JobOfferController extends AbstractController{

    #[Route('/job/offer/form', name: 'app_job_offer_form')]
    public function form(JobOfferRepository $jobOfferRepository): Response{
        return $this->render('candidate/_form.html.twig');

    }

    #[Route('/job/offer/insert', name: 'app_job_offer_insert', methods: ['POST'])]
    public function insertJob(Request $request,JobOfferRepository $jobOfferRepository,CompanyRepository $companyRepo
    ): Response {
        $offerType   = $request->request->get('offerType');
        $description = $request->request->get('description');
        $dateCreation = new \DateTime($request->request->get('date_creation'));
        $deadline    = new \DateTime($request->request->get('dealine'));
        $companyName = $request->request->get('companyName');
    
        $company = $companyRepo->findOneBy(['companyName' => $companyName]);
        $erreur=null;
        if(isset($company) && !empty($company)
            && isset($offerType) && !empty($offerType)
            &&isset($description) && !empty($description)
            &&isset($deadline) && !empty($deadline)){
                $jobOfferRepository->insertJob($offerType,$description,$dateCreation,$deadline,$company);
                return $this->redirectToRoute('app_job_portail');
        }else{
            $erreur="un ou plusieur(s) champ sont vides";
            return $this->render('candidate/_form.html.twig', ['datas' => $erreur]);
        }
    }
    

    #[Route('/job/offer/all', name: 'app_job_offer')]
    public function findAllJobController(JobOfferRepository $jobOfferRepository): Response{
        $jobDatas = $jobOfferRepository->findAllJob();
        return $this->render('job_offer/index.html.twig', ['datas' => $jobDatas]);
    }

    #[Route('/portail', name: 'app_job_portail')]
    public function portail(JobOfferRepository $jobOfferRepository): Response{
        $jobDatas = $jobOfferRepository->findAllJob();
        return $this->render('candidate/Portail_Candidate.html.twig', ['datas' => $jobDatas,]);
    }

}
