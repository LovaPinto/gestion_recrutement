<?php
namespace App\Controller;

use App\Repository\JobOfferRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class JobOfferController extends AbstractController
{

    #[Route('/job/offer/form', name: 'app_job_offer_form')]
    public function form(JobOfferRepository $jobOfferRepository): Response
    {
        return $this->render('candidate/_form.html.twig');

    }

    #[Route('/job/offer/insert', name: 'app_job_offer_insert', methods: ['POST'])]
    public function insertJob(Request $request, JobOfferRepository $jobOfferRepository, UsersRepository $usersRepository
    ): Response {
        $offerType    = $request->request->get('offerType');
        $description  = $request->request->get('description');
        $dateCreation = new \DateTime($request->request->get('date_creation'));
        $deadline     = new \DateTime($request->request->get('dealine'));
        $companyName  = $request->request->get('companyName');

        $company = $usersRepository->findOneBy(['id' => $companyName]);
        $erreur  = null;
        if (isset($company) && ! empty($company)
            && isset($offerType) && ! empty($offerType)
            && isset($description) && ! empty($description)
            && isset($deadline) && ! empty($deadline)) {
            $jobOfferRepository->insertJob($offerType, $description, $dateCreation, $deadline, $company);
            return $this->redirectToRoute('app_job_portail');
        } else {
            $erreur = "un ou plusieur(s) champ sont vides";
            return $this->render('candidate/_form.html.twig', ['datas' => $erreur]);
        }
    }

    #[Route('/job/offer/all', name: 'app_job_offer')]
    public function findAllJobController(JobOfferRepository $jobOfferRepository): Response
    {
        $jobDatas = $jobOfferRepository->findAllJob();
        return $this->render('job_offer/index.html.twig', ['datas' => $jobDatas]);
    }
    //portail
    #[Route('/portail', name: 'app_job_portail')]
    public function portail(JobOfferRepository $jobOfferRepository): Response
    {
        $jobDatas = $jobOfferRepository->findAllJob();
        return $this->render('candidate/Portail_candidate.html.twig', ['datas' => $jobDatas]);
    }

    #[Route('/portail/{offertype}', name: 'app_job_portail')]
    public function portai(JobOfferRepository $jobOfferRepository, string $offertype = null): Response
    {
        if ($offertype === null || $offertype === 'Toutes les offres') {
            $jobDatas = $jobOfferRepository->findAllJob();
        } else {
            $jobDatas = $jobOfferRepository->findAllJobByOfferType($offertype);
        }

        return $this->render('candidate/Portail_candidate.html.twig', [
            'datas'        => $jobDatas,
            'selectedType' => $offertype,
        ]);
    }

    #[Route('/job/offer/all/test', name: 'app_job_offer_test')]
    public function findAllJobTest(JobOfferRepository $jobOfferRepository): Response
    {
        $jobDatas = $jobOfferRepository->findAllJob();
        return $this->render('job_offer/test.html.twig', [
            'datas' => $jobDatas,
        ]);
    }

    #[Route('/ajoutOffre', name: 'ajoutOffre')]
    public function boutonAjoutOffre(): Response
    {
        return $this->render('/job_offer/ajoutOffre.html.twig');
    }

}
