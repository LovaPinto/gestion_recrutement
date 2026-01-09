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
            $this->addFlash('error', 'Vous devez être connecté pour postuler.');
            return $this->redirectToRoute('candidat_login');
        }

        $user = $this->entityManager->getRepository(Users::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('candidat_login');
        }

        if ($request->isMethod('POST')) {
            $candidacy = new Candidacy();
            $candidacy->setJobOffer($jobOffer);
            $candidacy->setUser($user);
            $candidacy->setDateCandidacy(new \DateTime());
            $candidacy->setStatus('en attente');

            // CV
            if ($cv = $request->files->get('cv_path')) {
                $candidacy->setCvPath(file_get_contents($cv->getPathname()));
            }

            // Pièce jointe
            if ($att = $request->files->get('attachement')) {
                $candidacy->setAttachement(file_get_contents($att->getPathname()));
            }

            // Portfolio
            $candidacy->setPortfolioLink($request->request->get('portfolio_link'));

            $this->entityManager->persist($candidacy);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre candidature a été envoyée avec succès.');
            return $this->redirectToRoute('app_candidate_dashboard');
        }

        return $this->render('candidacy/apply.html.twig', [
            'jobOffer' => $jobOffer
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
        return $this->render('candidacy/show.html.twig', [
            'candidacy' => $candidacy,
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
  #[Route('/candidacy/{id}/update', name: 'candidacy_update', methods: ['POST'])]
    public function updateCandidacy(
        Candidacy $candidacy,
        Request $request,
        EventDispatcherInterface $dispatcher
    ): Response {
        $oldStatus = $candidacy->getStatus();

        $status = $request->request->get('status');
        $note = $request->request->get('recruiter_note');
        $dateStr = $request->request->get('interview_date');

        if ($status) {
            $candidacy->setStatus($status);
        }

        $candidacy->setRecruiterNote($note ?: null);

        if ($dateStr) {
            try {
                $candidacy->setInterviewDate(new \DateTime($dateStr));
            } catch (\Exception) {
                $this->addFlash('error', 'Date d’entretien invalide.');
                return $this->redirectToRoute('candidacy_show', ['id' => $candidacy->getId()]);
            }
        } else {
            $candidacy->setInterviewDate(null);
        }

        $this->entityManager->flush();

        // 🔔 Envoi mail si le statut change
        if ($oldStatus !== $candidacy->getStatus()) {
            $sent = false;

            switch ($candidacy->getStatus()) {
                case 'invité à un entretien':
                    $sent = $this->mailer->sendInterviewEmail($candidacy);
                    break;

                case 'acceptée':
                    $sent = $this->mailer->sendAcceptedEmail($candidacy);
                    break;
            }

            if ($sent) {
                $this->addFlash('success', 'Candidature mise à jour. Email envoyé.');
            } else {
                $this->addFlash('warning', 'Candidature mise à jour. Échec de l’envoi de l’email.');
            }
        } else {
            $this->addFlash('success', 'Candidature mise à jour. Aucun changement de statut.');
        }

        return $this->redirectToRoute('candidacy_show', ['id' => $candidacy->getId()]);
    }


    
}