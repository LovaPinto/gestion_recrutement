<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class JobOfferController extends AbstractController
{
    private $entityManager;
    private $jobOfferRepository;

    // Injection des dépendances dans le constructeur
    public function __construct(EntityManagerInterface $entityManager, JobOfferRepository $jobOfferRepository)
    {
        $this->entityManager = $entityManager;
        $this->jobOfferRepository = $jobOfferRepository;
    }

    #[Route('/portail', name: 'app_job_portail_default')]
    public function portailDefault(Request $request): Response
    {
        // Récupérer les filtres depuis la requête GET (formulaire de recherche)
        $keyword = $request->query->get('keyword', '');
        $companyId = $request->query->get('company', '');
        $departmentId = $request->query->get('department', '');
        $offerType = $request->query->get('offerType', '');


        $jobOffers = $this->jobOfferRepository->findAllByFilter($keyword, $companyId, $departmentId, $offerType);
        $companyNames = $this->jobOfferRepository->findAllCompanyName();
        $departmentNames = $this->jobOfferRepository->findAllDepartmentName();
        $offerTypes = $this->jobOfferRepository->findAllOfferTypes();

        // Retourner la vue avec les résultats et les filtres
        return $this->render('candidate/Portail_candidate.html.twig', [
            'companyNames' => $companyNames,
            'departmentNames' => $departmentNames,
            'offerTypes' => $offerTypes,
            'jobOffers' => $jobOffers,
            'keyword' => $keyword,
            'selectedCompany' => $companyId,
            'selectedDepartment' => $departmentId,
            'selectedOfferType' => $offerType,
        ]);
    }

    #[Route('/portail/{offerType}', name: 'app_job_portail_by_type')]
    public function portailByType(string $offerType): Response
    {
        // Récupérer les entreprises, départements et offres par type
        $companyNames = $this->jobOfferRepository->findAllCompanyName();
        $departmentNames = $this->jobOfferRepository->findAllDepartmentName();
        $findAllJobOffersByType = $this->jobOfferRepository->findAllByOfferType($offerType);

        return $this->render('candidate/Portail_candidate.html.twig', [
            'companyNames' => $companyNames,
            'departmentNames' => $departmentNames,
            'jobOffers' => $findAllJobOffersByType,
        ]);
    }

    #[Route('/job/offers', name: 'app_job_offer')]
    public function showAllOffers(Request $request, PaginatorInterface $paginator): Response
    {

        $query = $this->jobOfferRepository->createQueryBuilder('o')
            ->orderBy('o.dateCreation', 'DESC')
            ->getQuery();

        // Pagination
        $jobOffers = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('job_offer/ShowAlljob.html.twig', [
            'jobOffers' => $jobOffers,
        ]);
    }
    // Affichage des détails d'une offre
    #[Route('/job/offer/{id}', name: 'job_offer_show')]
    public function showJobOfferDetails(JobOffer $jobOffer): Response
    {
        return $this->render('job_offer/showJobSkill.html.twig', [
            'jobOffer' => $jobOffer,
        ]);
    }
}
