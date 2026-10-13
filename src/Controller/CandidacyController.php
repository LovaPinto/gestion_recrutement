<?php

namespace App\Controller;

use App\Entity\Candidacy;
use App\Entity\JobOffer;
use App\Entity\Users;
use App\Event\CandidacyStatusChangedEvent;
use App\Repository\CandidacyRepository;
use App\Service\CandidacyMailer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Repository\JobOfferRepository;
use App\Service\CvTextExtractor;
use App\Service\AtsAiService;


final class CandidacyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CandidacyRepository $candidacyRepository,
        private CandidacyMailer $mailer
    ) {}

    /* ===================== POSTULER À UNE OFFRE ===================== */

#[Route('/job/{id}/apply', name: 'job_apply')]
public function apply(JobOffer $jobOffer, Request $request): Response
{
    $userId = $request->getSession()->get('user_id');

    if (!$userId) {
        return $this->render('candidacy/apply.html.twig', [
            'jobOffer' => $jobOffer,
            'result'   => 'error',
            'message'  => 'Vous devez être connecté pour postuler.',
        ]);
    }

    $user = $this->entityManager
        ->getRepository(Users::class)
        ->find($userId);

    if (!$user) {
        return $this->render('candidacy/apply.html.twig', [
            'jobOffer' => $jobOffer,
            'result'   => 'error',
            'message'  => 'Utilisateur introuvable.',
        ]);
    }

    /* 🔒 Vérification : candidature déjà existante */
    $existingCandidacy = $this->entityManager
        ->getRepository(Candidacy::class)
        ->findOneBy([
            'user'     => $user,
            'jobOffer' => $jobOffer
        ]);

    if ($existingCandidacy) {
        // Redirection vers la liste des offres avec paramètres GET pour afficher l'alerte
        return $this->redirectToRoute('app_job_offer', [
            'result'  => 'warning',
            'message' => urlencode('Désolé, vous avez déjà postulé à cette offre.')
        ]);
    }

    if ($request->isMethod('POST')) {
        try {
            $candidacy = new Candidacy();
            $candidacy->setJobOffer($jobOffer);
            $candidacy->setUser($user);
            $candidacy->setDateCandidacy(new \DateTime());

            // Vérification de la deadline
            if ($jobOffer->getDeadline() !== null && new \DateTime() > $jobOffer->getDeadline()) {
                $candidacy->setStatus(Candidacy::STATUS_REFUSED);
            } else {
                $candidacy->setStatus(Candidacy::STATUS_PENDING);
            }

            // Fichiers
            if ($cv = $request->files->get('cv_path')) {
                $candidacy->setCvPath(file_get_contents($cv->getPathname()));
            }

            if ($att = $request->files->get('attachement')) {
                $candidacy->setAttachement(file_get_contents($att->getPathname()));
            }

            // Lien portfolio et message de motivation
            $candidacy->setPortfolioLink($request->request->get('portfolio_link'));
            $candidacy->setReason($request->request->get('reason'));

            $this->entityManager->persist($candidacy);
            $this->entityManager->flush();

            return $this->render('candidacy/apply.html.twig', [
                'jobOffer' => $jobOffer,
                'user'     => $user,
                'result'   => 'success',
                'message'  => 'Votre candidature a été envoyée avec succès.',
            ]);

        } catch (\Exception $e) {
            return $this->render('candidacy/apply.html.twig', [
                'jobOffer' => $jobOffer,
                'user'     => $user,
                'result'   => 'error',
                'message'  => 'Une erreur est survenue. Veuillez réessayer.',
            ]);
        }
    }

    return $this->render('candidacy/apply.html.twig', [
        'jobOffer' => $jobOffer,
        'user'     => $user,
    ]);
}

    /* ===================== LISTE DES CANDIDATURES ===================== */
    #[Route('/job/offer/{id}/candidacies', name: 'job_offer_candidacies')]
    public function showCandidacies(
        JobOffer $jobOffer,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $this->candidacyRepository->createQueryBuilder('c')
            ->where('c.jobOffer = :jobOffer')
            ->setParameter('jobOffer', $jobOffer)
            ->orderBy('c.dateCandidacy', 'DESC')
            ->getQuery();

        $candidacies = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('candidacy/list.html.twig', [
            'jobOffer' => $jobOffer,
            'candidacies' => $candidacies,
        ]);
    }

    /* ===================== DÉTAIL ===================== */
    #[Route('/candidacy/{id}', name: 'candidacy_show')]
    public function show(Candidacy $candidacy): Response
    {
            $candidate = $candidacy->getUser() ? $candidacy->getUser()->getCandidate() : null;
        return $this->render('candidacy/show.html.twig', [
             'candidacy' => $candidacy,
        'candidate' => $candidate,
        ]);
    }

    /* ===================== TÉLÉCHARGEMENT FICHIERS ===================== */
    #[Route('/candidacy/{id}/file/{type}', name: 'candidacy_file')]
    public function downloadFile(Candidacy $candidacy, string $type): Response
    {
        if (!in_array($type, ['cv', 'attachement'])) {
            throw $this->createNotFoundException();
        }

        $data = $type === 'cv' ? $candidacy->getCvPath() : $candidacy->getAttachement();
        if (!$data) {
            throw $this->createNotFoundException();
        }

        if (is_resource($data)) {
            rewind($data);
            $data = stream_get_contents($data);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($data) ?: 'application/octet-stream';
        $filename = strtoupper($type).'_'.$candidacy->getId();

        return new Response($data, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    }

    /* ===================== UPDATE RH + OBSERVER ===================== */
#[Route('/candidacy/{id}/update', name: 'candidacy_update', methods: ['GET','POST'])]
public function updateCandidacy(
    Candidacy $candidacy,
    Request $request,
    EntityManagerInterface $entityManager,
    CandidacyMailer $mailer
): Response {
    $oldStatus = $candidacy->getStatus();

    if ($request->isMethod('POST')) {
        $status = $request->request->get('status');
        $note   = $request->request->get('recruiter_note');

        // 🚫 Vérification des transitions interdites
        if ($oldStatus === Candidacy::STATUS_ACCEPTED && $status !== Candidacy::STATUS_ACCEPTED) {
            $this->addFlash('warning', 'Statut "acceptée" ne peut pas être changé.');
            return $this->redirectToRoute('candidacy_update', ['id' => $candidacy->getId()]);
        }

        if ($oldStatus === Candidacy::STATUS_INTERVIEW && $status === Candidacy::STATUS_PENDING) {
            $this->addFlash('warning', 'Statut "invité à un entretien" ne peut pas revenir à "en attente".');
            return $this->redirectToRoute('candidacy_update', ['id' => $candidacy->getId()]);
        }

        // ✅ Mise à jour statut et note
        $candidacy->setStatus($status);
        $candidacy->setRecruiterNote($note ?: null);

        // ✅ Date entretien uniquement si statut = "invité à un entretien"
        if ($status === Candidacy::STATUS_INTERVIEW) {
            $interviewDate = $request->request->get('interview_date');
            $candidacy->setInterviewDate($interviewDate ? new \DateTime($interviewDate) : null);
        } else {
            $candidacy->setInterviewDate(null);
        }

        $entityManager->flush();

        // 📧 Emails
        $emailSent = false;
        try {
            if ($oldStatus !== $status) {
                if ($status === Candidacy::STATUS_INTERVIEW) {
                    $mailer->sendInterviewEmail($candidacy);
                    $emailSent = true;
                }
                if ($status === Candidacy::STATUS_ACCEPTED) {
                    $mailer->sendAcceptedEmail($candidacy);
                    $emailSent = true;
                }
            }
        } catch (\Throwable) {
            $emailSent = false;
        }

        $this->addFlash($emailSent ? 'success' : 'warning', 'Candidature mise à jour.' . ($emailSent ? ' Email envoyé.' : ' Email NON envoyé.'));
        return $this->redirectToRoute('offre_candidatures', ['id' => $candidacy->getId()]);
    }

    return $this->render('candidacy/update.html.twig', [
        'candidacy' => $candidacy,
        'candidate' => $candidacy->getUser()?->getCandidate(),
    ]);
}



#[Route('/offre/{id}/candidatures', name: 'offre_candidatures')]
public function candidaturesParOffre(
    int $id,
    JobOfferRepository $jobOfferRepository,
    CandidacyRepository $candidacyRepository,
    AtsAiService $atsAiService,
    CvTextExtractor $cvTextExtractor
): Response
{
    // ===================== 1. Récupérer l’offre =====================
    $offer = $jobOfferRepository->find($id);
    if (!$offer) {
        throw $this->createNotFoundException('Offre introuvable');
    }

    // ===================== 2. Construire le texte de comparaison =====================
    $jobText = strtolower(trim(
        ($offer->getDescription() ?? '') . ' ' .
        (is_array($offer->getJobSkills()) ? implode(' ', $offer->getJobSkills()) : ($offer->getJobSkills() ?? ''))
    ));

    // ===================== 3. Récupérer toutes les candidatures en attente =====================
    $pendingCandidacies = $candidacyRepository->findBy([
        'jobOffer' => $offer,
        'status'   => Candidacy::STATUS_PENDING
    ]);

    // ===================== 4. Calculer le score ATS =====================
    foreach ($pendingCandidacies as $candidacy) {
        $score = null;

        if ($candidacy->getCvPath()) {
            $tmpPath = tempnam(sys_get_temp_dir(), 'cv_');
            file_put_contents($tmpPath, $candidacy->getCvPath());

            $mime = $candidacy->getCvMimeType() ?? mime_content_type($tmpPath);
            $cvText = $cvTextExtractor->extract($tmpPath, $mime);
            $score = $atsAiService->score($cvText, $jobText);

            unlink($tmpPath);
        }

        $candidacy->setAtsScore($score);
    }

    // ===================== 5. Trier par score décroissant =====================
    usort($pendingCandidacies, fn($a, $b) => ($b->getAtsScore() ?? 0) <=> ($a->getAtsScore() ?? 0));

    // ===================== 6. Récupérer les autres statuts =====================
    $interview = $candidacyRepository->findBy([
        'jobOffer' => $offer,
        'status'   => Candidacy::STATUS_INTERVIEW
    ]);

    $accepted = $candidacyRepository->findBy([
        'jobOffer' => $offer,
        'status'   => Candidacy::STATUS_ACCEPTED
    ]);

    $refused = $candidacyRepository->findBy([
        'jobOffer' => $offer,
        'status'   => Candidacy::STATUS_REFUSED
    ]);

    // ===================== 7. Rendu =====================
    return $this->render('department/suivieCandidature.html.twig', [
        'offer'     => $offer,
        'pending'   => $pendingCandidacies,  // trié par score ATS
        'interview' => $interview,
        'accepted'  => $accepted,
        'refused'   => $refused,
    ]);
}


#[Route('/Entretien', name: 'interview')]
public function showAllInterview(Request $request): Response
{
    $today = new \DateTime('today 00:00:00');

    // ===================== Récupération des filtres =====================
    $startDate = $request->query->get('start_date');
    $endDate   = $request->query->get('end_date');

    // Si start_date vide, on met aujourd'hui minuit
    if (!$startDate) {
        $start = $today;
        $startDate = $start->format('Y-m-d\TH:i'); // pour input datetime-local
    } else {
        $start = new \DateTime($startDate);
    }

    $qb = $this->candidacyRepository->createQueryBuilder('c')
        ->join('c.jobOffer', 'o')
        ->addSelect('o')
        ->where('c.interviewDate IS NOT NULL')
        ->andWhere('c.status = :status')
        ->setParameter('status', Candidacy::STATUS_INTERVIEW)
        ->andWhere('c.interviewDate >= :start')
        ->setParameter('start', $start)
        ->orderBy('c.interviewDate', 'ASC');

    // Si l’utilisateur a explicitement choisi end_date, on l’ajoute
    if ($endDate) {
        $end = new \DateTime($endDate);
        $qb->andWhere('c.interviewDate <= :end')
           ->setParameter('end', $end);
    }

    $interviews = $qb->getQuery()->getResult();

    // ===================== Séparer upcoming et past =====================
    $upcoming = [];
    $past = [];

    $now = new \DateTime();
    foreach ($interviews as $c) {
        if ($c->getInterviewDate() < $now) {
            $past[] = $c;
        } else {
            $upcoming[] = $c;
        }
    }

    return $this->render('candidacy/interviews.html.twig', [
        'upcoming'  => $upcoming,
        'past'      => $past,
        'startDate' => $startDate,
        'endDate'   => $endDate,
        'now'       => $now,
    ]);
}


}
