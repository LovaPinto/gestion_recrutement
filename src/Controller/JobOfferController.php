<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\Company;
use App\Entity\Department;
use App\Entity\Users;
use App\Repository\JobOfferRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use app\Repository\CompanyRepository;
use app\Repository\DepartmentRepository;

final class JobOfferController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private JobOfferRepository $jobOfferRepository;
    private CompanyRepository $companyRepository;
    private DepartmentRepository $departmentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        JobOfferRepository $jobOfferRepository
    ) {
        $this->entityManager = $entityManager;
        $this->jobOfferRepository = $jobOfferRepository;
        $this->companyRepository = $entityManager->getRepository(Company::class);
        $this->departmentRepository = $entityManager->getRepository(Department::class);
    }

    /* ===================== FORMULAIRE OFFRE ===================== */

    #[Route('/job/offer/form', name: 'app_job_offer_form')]
    public function form(): Response
    {
        return $this->render('candidate/_form.html.twig');
    }

    #[Route('/job/offer/insert', name: 'app_job_offer_insert', methods: ['POST'])]
    public function insertJob( Request $request,UsersRepository $usersRepository
    ): Response {
        $offerType    = $request->request->get('offerType');
        $description  = $request->request->get('description');
        $dateCreation = new \DateTime();
        $deadline     = new \DateTime($request->request->get('deadline'));
        $companyId    = $request->request->get('companyName');

        $company = $usersRepository->find($companyId);

        if ($offerType && $description && $deadline && $company) {
            $this->jobOfferRepository->insertJob(
                $offerType,
                $description,
                $dateCreation,
                $deadline,
                $company
            );

            return $this->redirectToRoute('app_job_portail_default');
        }

        return $this->render('candidate/_form.html.twig', [
            'datas' => 'Un ou plusieurs champs sont vides',
        ]);
    }

    /* ===================== PORTAIL ===================== */
    #[Route('/portail', name: 'app_job_portail_default')]
    public function portail(Request $request): Response
    {
        $keyword      = $request->query->get('keyword', '');
        $companyId    = $request->query->get('company', '');
        $departmentId = $request->query->get('department', '');
        $offerType    = $request->query->get('offerType', '');

        $jobOffers = $this->jobOfferRepository
            ->findAllByFilter($keyword, $companyId, $departmentId, $offerType);

        return $this->render('candidate/Portail_candidate.html.twig', [
            'companyNames'      => $this->companyRepository->findAllCompanyName(),
            'departmentNames'   => $this->departmentRepository->findAllDepartmentName(),
            'offerTypes'        => $this->jobOfferRepository->findAllOfferTypes(),
            'jobOffers'         => $jobOffers,
            'keyword'           => $keyword,
            'selectedCompany'   => $companyId,
            'selectedDepartment'=> $departmentId,
            'selectedOfferType' => $offerType,
        ]);
    }

    /* ===================== LISTE DES OFFRES (ADMIN) ===================== */
    #[Route('/job/offers', name: 'app_job_offer')]
    public function showAllOffers(
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $this->jobOfferRepository
            ->createQueryBuilder('o')
            ->orderBy('o.dateCreation', 'DESC')
            ->getQuery();

        $jobOffers = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );
        $jobOffers = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('job_offer/ShowAlljob.html.twig', [
            'jobOffers' => $jobOffers,
        ]);
    }

    /* ===================== DÉTAIL D’UNE OFFRE ===================== */
    #[Route('/job/offer/{id}', name: 'job_offer_show')]
    public function showJobOfferDetails(JobOffer $jobOffer): Response
    {
        return $this->render('job_offer/showJobSkill.html.twig', [
            'jobOffer' => $jobOffer,
        ]);
    }

    /* ===================== CRÉATION OFFRE (WIZARD 5 ÉTAPES) ===================== */
    #[Route(
        '/create_job/{step}', name: 'job_offer_create',defaults: ['step' => 1],requirements: ['step' => '\d+']
    )]
    public function create(Request $request, int $step): Response
    {
        $session  = $request->getSession();
        $jobOffer = $session->get('job_offer', new JobOffer());

        if ($request->isMethod('POST')) {

            switch ($step) {

                case 1:
                    $jobOffer->setTitle($request->request->get('title'));

                    $company = $this->companyRepository->find(
                        (int) $request->request->get('company')
                    );
                    $department = $this->departmentRepository->find(
                        (int) $request->request->get('department')
                    );

                    if (!$company || !$department) {
                        throw new \Exception('Entreprise ou département introuvable.');
                    }

                    $jobOffer->setCompany($company);
                    $jobOffer->setDepartment($department);
                    break;

                case 2:
                    $jobOffer->setDescription(
                        $request->request->get('description')
                    );
                    break;

                case 3:
                    $skills = array_filter(
                        array_map('trim', explode("\n", $request->request->get('skills')))
                    );
                    $jobOffer->setJobSkills($skills);
                    break;

                case 4:
                    $jobOffer->setOfferType($request->request->get('offer_type'));
                    $jobOffer->setExperienceLevel($request->request->get('experience_level'));
                    $jobOffer->setDeadline(new \DateTime($request->request->get('deadline')));
                    $jobOffer->setStatus($request->request->get('status'));
                    break;

                case 5:
                    $jobOffer->setDateCreation(new \DateTime());
                case 5:
                    $jobOffer->setDateCreation(new \DateTime());
                case 5:
                    $jobOffer->setDateCreation(new \DateTime());

                    // Exemple : utilisateur connecté
                    $user = $this->entityManager
                        ->getRepository(Users::class)
                        ->find(1);

                    $jobOffer->setUser($user);

                    $this->entityManager->persist($jobOffer);
                    $this->entityManager->flush();
                    $this->entityManager->persist($jobOffer);
                    $this->entityManager->flush();

                    $session->remove('job_offer');
                    return $this->redirectToRoute('app_job_portail_default');
                    $session->remove('job_offer');
                    return $this->redirectToRoute('app_job_portail_default');
            }

            $session->set('job_offer', $jobOffer);

            return $this->redirectToRoute('job_offer_create', [
                'step' => $step + 1
            ]);
        }

        return $this->render('job_offer/insert_job.html.twig', [
            'step'        => $step,
            'jobOffer'    => $jobOffer,
            'companies'   => $this->entityManager->getRepository(Company::class)->findAll(),
            'departments' => $this->entityManager->getRepository(Department::class)->findAll(),
            'offerTypes'  => $this->jobOfferRepository->findAllOfferTypes(),
        ]);
    }

   #[Route('/job-offers1', name: 'job_offer_list_RH')]
public function index(Request $request): Response
{
    $keyword        = $request->query->get('keyword', null);
    $companyName    = $request->query->get('company', null);
    $departmentName = $request->query->get('department', null);
    $offerType      = $request->query->get('offerType', null);
    $status         = $request->query->get('status', null);

    $jobOffers = $this->jobOfferRepository->findAllJobByFilter(
        $keyword,
        $companyName,
        $departmentName,
        $offerType,
        $status
    );

    return $this->render('job_offer/ajoutOffre.html.twig', [
        'jobOffers'         => $jobOffers,
        'keyword'           => $keyword,
        'selectedCompany'   => $companyName,
        'selectedDepartment'=> $departmentName,
        'selectedOfferType' => $offerType,
        'selectedStatus'    => $status,
    ]);
}



}
