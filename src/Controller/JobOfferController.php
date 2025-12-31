<?php
namespace App\Controller;

<<<<<<< HEAD
use App\Entity\JobOffer;
use App\Entity\Company;
use App\Entity\Department;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
=======
use App\Repository\JobOfferRepository;
use App\Repository\UsersRepository;
>>>>>>> feat/entrprise
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
<<<<<<< HEAD
use App\Entity\Users;   

final class JobOfferController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private JobOfferRepository $jobOfferRepository;

    public function __construct(EntityManagerInterface $entityManager, JobOfferRepository $jobOfferRepository)
    {
        $this->entityManager = $entityManager;
        $this->jobOfferRepository = $jobOfferRepository;
    }

    /* ===================== PORTAIL ===================== */

    #[Route('/portail', name: 'app_job_portail_default')]
    public function portailDefault(Request $request): Response
    {
        $keyword = $request->query->get('keyword', '');
        $companyId = $request->query->get('company', '');
        $departmentId = $request->query->get('department', '');
        $offerType = $request->query->get('offerType', '');

        $jobOffers = $this->jobOfferRepository
            ->findAllByFilter($keyword, $companyId, $departmentId, $offerType);
=======

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
>>>>>>> feat/entrprise

        return $this->render('candidate/Portail_candidate.html.twig', [
            'companyNames' => $this->jobOfferRepository->findAllCompanyName(),
            'departmentNames' => $this->jobOfferRepository->findAllDepartmentName(),
            'offerTypes' => $this->jobOfferRepository->findAllOfferTypes(),
            'jobOffers' => $jobOffers,
            'keyword' => $keyword,
            'selectedCompany' => $companyId,
            'selectedDepartment' => $departmentId,
            'selectedOfferType' => $offerType,
        ]);
    }

    /* ===================== LISTE OFFRES ===================== */

    #[Route('/job/offers', name: 'app_job_offer')]
    public function showAllOffers(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->jobOfferRepository
            ->createQueryBuilder('o')
            ->orderBy('o.dateCreation', 'DESC')
            ->getQuery();

        $jobOffers = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('job_offer/ShowAlljob.html.twig', [
            'jobOffers' => $jobOffers,
        ]);
    }

    /* ===================== DÉTAIL OFFRE ===================== */

    #[Route('/job/offer/{id}', name: 'job_offer_show')]
    public function showJobOfferDetails(JobOffer $jobOffer): Response
    {
        return $this->render('job_offer/showJobSkill.html.twig', [
            'jobOffer' => $jobOffer,
        ]);
    }

    /* ===================== CRÉATION OFFRE (WIZARD 5 STEPS) ===================== */

      #[Route('/create_job/{step}', name: 'job_offer_create', defaults: ['step' => 1], requirements: ['step' => '\d+'])]
    public function create(Request $request, int $step): Response
    {
        $session = $request->getSession();
        $jobOffer = $session->get('job_offer', new JobOffer());

        if ($request->isMethod('POST')) {
            switch ($step) {
                case 1:
                    $jobOffer->setTitle($request->request->get('title'));

                    $companyId = (int)$request->request->get('company');
                    $departmentId = (int)$request->request->get('department');

                    $company = $this->entityManager->getRepository(Company::class)->find($companyId);
                    $department = $this->entityManager->getRepository(Department::class)->find($departmentId);

                    if (!$company || !$department) {
                        throw new \Exception('Entreprise ou département introuvable.');
                    }

                    $jobOffer->setCompany($company);
                    $jobOffer->setDepartment($department);
                    break;

                case 2:
                    $jobOffer->setDescription($request->request->get('description'));
                    break;

                case 3:
                    $skills = array_filter(array_map('trim', explode("\n", $request->request->get('skills'))));
                    $jobOffer->setJobSkills($skills);
                    break;

                case 4:
                    $jobOffer->setOfferType($request->request->get('offer_type'));
                    $jobOffer->setExperienceLevel($request->request->get('experience_level'));

                    $deadline = $request->request->get('deadline');
                    if ($deadline) {
                        $jobOffer->setDeadline(new \DateTime($deadline));
                    }

                    $jobOffer->setStatus($request->request->get('status'));
                    break;

             case 5:
    $jobOffer->setDateCreation(new \DateTime());

    // 🟢 Ajout du user_id 1
    $user = $this->entityManager->getRepository(Users::class)->find(1);
    $jobOffer->setUser($user);

    $this->entityManager->persist($jobOffer);
    $this->entityManager->flush();

    $session->remove('job_offer');
    return $this->redirectToRoute('app_job_portail_default');

            }

            $session->set('job_offer', $jobOffer);
            return $this->redirectToRoute('job_offer_create', ['step' => $step + 1]);
        }

        return $this->render('job_offer/insert_job.html.twig', [
            'step' => $step,
            'jobOffer' => $jobOffer,
            'companies' => $this->entityManager->getRepository(Company::class)->findAll(),
            'departments' => $this->entityManager->getRepository(Department::class)->findAll(),
            'offerTypes' => $this->jobOfferRepository->findAllOfferTypes(),
        ]);
    }
}
