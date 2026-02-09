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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\CvTextExtractor;
use App\Service\AtsAiService;
use App\Repository\CandidacyRepository;
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
    PaginatorInterface $paginator,
    JobOfferRepository $jobOfferRepository
): Response {

    $search = $request->query->get('q');

    $query = $jobOfferRepository->createQueryBuilder('o')
        ->where('o.status = :status')
        ->andWhere('o.isVisible = true')
        ->andWhere('o.deadline IS NULL OR o.deadline >= :today')
        ->setParameter('status', JobOffer::STATUS_PUBLIEE)
        ->setParameter('today', new \DateTime())
        ->orderBy('o.deadline', 'ASC');

    if ($search) {
        $query->andWhere('o.title LIKE :q OR o.description LIKE :q')
              ->setParameter('q', '%' . $search . '%');
    }

    $jobOffers = $paginator->paginate(
        $query->getQuery(),
        $request->query->getInt('page', 1),
        5
    );

    // Récupération du message et du résultat depuis GET
    $result  = $request->query->get('result');
    $message = $request->query->get('message') ? urldecode($request->query->get('message')) : null;

    return $this->render('job_offer/ShowAlljob.html.twig', [
        'jobOffers' => $jobOffers,
        'result'    => $result,
        'message'   => $message,
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
 #[Route('/create_job/{step}', name: 'job_offer_create', requirements: ['step' => '\d+'], defaults: ['step' => 1])]
    public function createJob(
        int $step,
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response
    {
        // Récupérer la session entreprise
        $companySession = $session->get('company');
        if (!$companySession) {
            return $this->render('job_offer/insert_job.html.twig', [
                'step' => 1,
                'departments' => [],
                'session_error' => '⚠️ Accès refusé.'
            ]);
        }

        $company = $em->getRepository(Company::class)->find($companySession['id']);
        if (!$company) {
            return $this->render('job_offer/insert_job.html.twig', [
                'step' => 1,
                'departments' => [],
                'session_error' => 'Entreprise introuvable.'
            ]);
        }

        // Initialisation session job_offer_data
        if (!$session->has('job_offer_data')) {
            $session->set('job_offer_data', []);
        }
        $jobData = $session->get('job_offer_data');

        // Charger les départements pour l'étape 2
        $departments = $step === 2 ? $em->getRepository(Department::class)->findBy(['company' => $company]) : [];

        // POST
        if ($request->isMethod('POST')) {

            if ($step === 1) {
                // Étape 1 : titre, type, level, description
                $jobData['title'] = trim($request->request->get('title'));
                $jobData['offerType'] = $request->request->get('offerType');
                $jobData['experience_level'] = $request->request->get('experience_level');
                $jobData['description'] = trim($request->request->get('description'));

                // Sauvegarder en session et passer à l'étape 2
                $session->set('job_offer_data', $jobData);
                return $this->redirectToRoute('job_offer_create', ['step' => 2]);
            }

            if ($step === 2) {
                // Étape 2 : département, responsabilités, compétences, deadline
                $jobData['department'] = (int) $request->request->get('department');
                $jobData['responsability'] = trim($request->request->get('responsability'));
                $skillsText = $request->request->get('job_skills', '');
                $jobData['job_skills'] = array_values(array_filter(array_map('trim', explode("\n", $skillsText))));
                $jobData['deadline'] = $request->request->get('deadline');

                // Validation
                $required = ['title','offerType','experience_level','description','department','responsability'];
                foreach ($required as $field) {
                    if (empty($jobData[$field])) {
                        return $this->render('job_offer/insert_job.html.twig', [
                            'step' => 2,
                            'departments' => $departments,
                            'error' => '⚠️ Veuillez remplir tous les champs obligatoires.'
                        ]);
                    }
                }

                // Récupération du département
                $department = $em->getRepository(Department::class)->find($jobData['department']);
                if (!$department || $department->getCompany()->getId() !== $company->getId()) {
                    return $this->render('job_offer/insert_job.html.twig', [
                        'step' => 2,
                        'departments' => $departments,
                        'error' => 'Département invalide.'
                    ]);
                }

                // Création de l'offre
                $jobOffer = new JobOffer();
                $jobOffer
                    ->setTitle($jobData['title'])
                    ->setOfferType($jobData['offerType'])
                    ->setExperienceLevel($jobData['experience_level'])
                    ->setDescription($jobData['description'])
                    ->setResponsability($jobData['responsability'])
                    ->setJobSkills($jobData['job_skills'])
                    ->setCompany($company)
                    ->setDepartment($department)
                    ->setStatus(JobOffer::STATUS_EN_ATTENTE)
                    ->setDateCreation(new \DateTime());

                if (!empty($jobData['deadline'])) {
                    $jobOffer->setDeadline(new \DateTime($jobData['deadline']));
                }

                $em->persist($jobOffer);
                $em->flush();

                $session->remove('job_offer_data');

                $this->addFlash('success', 'L\'offre a été créée avec succès !');
                return $this->redirectToRoute('dashboardManager');
            }
        }

   
        return $this->render('job_offer/insert_job.html.twig', [
            'step' => $step,
            'departments' => $departments
        ]);
    }
//offre par company 
  #[Route('/company/job-offers', name: 'company_job_offers')]
    public function list(
        Request $request,
        EntityManagerInterface $em,
        JobOfferRepository $jobOfferRepository
    ): Response {

        $companySession = $request->getSession()->get('company');

        if (!$companySession) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('loginCompany');
        }

        $company = $em->getRepository(Company::class)
                      ->find($companySession['id']);

        if (!$company) {
            throw $this->createNotFoundException('Entreprise introuvable');
        }


        $status = $request->query->get('status');

        if ($status) {
            $jobOffers = $jobOfferRepository
                ->findByCompanyAndStatus($company, $status);
        } else {
            $jobOffers = $jobOfferRepository
                ->findByCompany($company);
        }

        return $this->render('job_offer/ajoutOffre.html.twig', [
            'jobOffers' => $jobOffers,
            'company'   => $company,
            'status'    => $status,
        ]);
    }
  #[Route('/job_offer/hide/{id}', name: 'job_offer_hide', methods: ['POST'])]
    public function hide(JobOffer $jobOffer, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('hide' . $jobOffer->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('company_job_offers');
        }
        $jobOffer->setStatus(JobOffer::STATUS_SUPPRIMEE);
        $em->flush();

        $this->addFlash('success', 'Offre masquée avec succès.');
        return $this->redirectToRoute('company_job_offers');
    }
    //validation de l'offre 
#[Route('/job_offer/pending/{id}', name: 'job_offer_pending')]
public function pendingOffer(
    JobOffer $jobOffer,
    Request $request,
    EntityManagerInterface $em
): Response
{
    if ($jobOffer->getStatus() !== 'en attente') {
        return $this->render('job_offer/pending.html.twig', [
            'offer' => $jobOffer,
            'result' => 'error',
            'message' => 'Cette offre n’est pas en attente.'
        ]);
    }

    if ($request->isMethod('POST')) {

        try {
            $jobOffer->setTitle($request->request->get('title'));
            $jobOffer->setDescription($request->request->get('description'));
            $jobOffer->setOfferType($request->request->get('offerType'));
            $jobOffer->setResponsability($request->request->get('responsability'));
            $jobOffer->setExperienceLevel($request->request->get('experience_level'));

            $jobOffer->setJobSkills(
                array_values(array_filter(array_map(
                    'trim',
                    explode("\n", $request->request->get('job_skills', ''))
                )))
            );

            if ($request->request->get('deadline')) {
                $jobOffer->setDeadline(new \DateTime($request->request->get('deadline')));
            }

            // 🔒 Manager
            $jobOffer->setRoleId(2);

            if ($request->request->get('action') === 'publish') {
                $jobOffer->setStatus('publiée');
            } else {
                $jobOffer->setStatus('refusée');
            }

            $em->flush();

            return $this->render('job_offer/pending.html.twig', [
                'offer' => $jobOffer,
                'result' => 'success',
                'message' => 'Offre publiée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->render('job_offer/pending.html.twig', [
                'offer' => $jobOffer,
                'result' => 'error',
                'message' => 'Une erreur est survenue'
            ]);
        }
    }

    return $this->render('job_offer/pending.html.twig', [
        'offer' => $jobOffer
    ]);
}


   // ===== RANKING CV =====
#[Route('/suivieCandidat/{id}', name: 'suivieCandidate')]
public function suivieCandidat(
    int $id,
    JobOfferRepository $jobOfferRepository,
    CandidacyRepository $candidacyRepository,
    CvTextExtractor $cvExtractor,
    AtsAiService $atsService,
    EntityManagerInterface $em
): Response {

    $offer = $jobOfferRepository->find($id);

    if (!$offer) {
        throw $this->createNotFoundException('Offre introuvable');
    }

    // Récupère toutes les candidatures pour cette offre
    $candidacies = $candidacyRepository->findBy(['jobOffer' => $offer]);

    // Texte de l'offre pour le scoring ATS
    $jobText = strtolower(
        $offer->getTitle() . ' ' .
        $offer->getDescription() . ' ' .
        implode(' ', $offer->getJobSkills() ?? []) . ' ' .
        ($offer->getExperienceLevel() ?? '') . ' ' .
        ($offer->getResponsability() ?? '')
    );

    // Calcul ATS pour chaque candidature
    foreach ($candidacies as $candidacy) {
        $cvBlob = $candidacy->getCvPath(); // BLOB depuis la DB
        $tempFile = null;

        if (!$cvBlob) {
            $candidacy->setAtsScore(null);
            continue;
        }

        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'cv_');
            file_put_contents($tempFile, $cvBlob);

            $mimeType = mime_content_type($tempFile) ?: 'application/pdf';
            $cvText = $cvExtractor->extract($tempFile, $mimeType);

            $score = $atsService->score($cvText, $jobText);
            $candidacy->setAtsScore(is_numeric($score) ? (float)$score : 0.0);

        } catch (\Throwable $e) {
            $candidacy->setAtsScore(null);
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    $em->flush();

    // Séparer les candidatures selon le status
    $pending   = array_filter($candidacies, fn($c) => $c->getStatus() === 'en attente');
    $published = array_filter($candidacies, fn($c) => $c->getStatus() === 'publiée');
    $taken     = array_filter($candidacies, fn($c) => $c->getStatus() === 'déjà prise');

    // Tri par score ATS (du meilleur au moins bon)
    $sortByScore = fn($a, $b) => ($b->getAtsScore() ?? -1) <=> ($a->getAtsScore() ?? -1);
    usort($pending, $sortByScore);
    usort($published, $sortByScore);
    usort($taken, $sortByScore);

    return $this->render('department/suivieCandidature.html.twig', [
        'offer'     => $offer,
        'pending'   => $pending,
        'interview' => $published, 
        'accepted'  => $taken,     
    ]);
}


}



