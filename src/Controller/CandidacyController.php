<?php

namespace App\Controller;

use App\Entity\Candidacy;
use App\Entity\JobOffer;
use App\Repository\CandidacyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CandidacyController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CandidacyRepository $candidacyRepository;

    public function __construct(EntityManagerInterface $entityManager, CandidacyRepository $candidacyRepository)
    {
        $this->entityManager = $entityManager;
        $this->candidacyRepository = $candidacyRepository;
    }

    /* ===================== POSTULER À UNE OFFRE ===================== */
    #[Route('/job/{id}/apply', name: 'job_apply')]
    public function apply(JobOffer $jobOffer, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $candidacy = new Candidacy();
            $candidacy->setJobOffer($jobOffer);
            $candidacy->setDateCandidacy(new \DateTime());
            $candidacy->setStatus('en attente');

            // CV en BLOB
            $cv = $request->files->get('cv_path');
            if ($cv) {
                $content = file_get_contents($cv->getPathname());
                $candidacy->setCvPath($content);
            }

            // Pièce jointe en BLOB
            $att = $request->files->get('attachement');
            if ($att) {
                $content = file_get_contents($att->getPathname());
                $candidacy->setAttachement($content);
            }

            // Portfolio
            $candidacy->setPortfolioLink($request->request->get('portfolio_link'));

            $this->entityManager->persist($candidacy);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre candidature a été envoyée avec succès.');
            return $this->redirectToRoute('app_job_portail_default');
        }

        return $this->render('candidacy/apply.html.twig', [
            'jobOffer' => $jobOffer
        ]);
    }

    /* ===================== LISTE DES CANDIDATURES ===================== */
    #[Route('/job/offer/{id}/candidacies', name: 'job_offer_candidacies')]
    public function showCandidacies(JobOffer $jobOffer, Request $request, PaginatorInterface $paginator): Response
    {
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

    /* ===================== DÉTAIL D’UNE CANDIDATURE ===================== */
    #[Route('/candidacy/{id}', name: 'candidacy_show')]
    public function show(Candidacy $candidacy): Response
    {
        return $this->render('candidacy/show.html.twig', [
            'candidacy' => $candidacy,
        ]);
    }

    /* ===================== AFFICHER / TÉLÉCHARGER FICHIER ===================== */
#[Route('/candidacy/{id}/file/{type}', name: 'candidacy_file')]
public function downloadCandidacyFile(Candidacy $candidacy, string $type): Response
{
    if (!in_array($type, ['cv', 'attachement'])) {
        throw $this->createNotFoundException('Type de fichier inconnu.');
    }

    $data = ($type === 'cv') ? $candidacy->getCvPath() : $candidacy->getAttachement();
    if (!$data) {
        throw $this->createNotFoundException('Fichier non trouvé.');
    }

    // Lire le BLOB si c'est un resource
    if (is_resource($data)) {
        rewind($data);
        $data = stream_get_contents($data);
    }

    // Détecter le type mime pour PDF, image, Word
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($data) ?: 'application/octet-stream';

    // Nom du fichier avec extension possible
    $filename = ($type === 'cv') ? 'CV_'.$candidacy->getId() : 'Attachement_'.$candidacy->getId();

    // Ajouter l'extension si c'est un PDF
    if ($mime === 'application/pdf') {
        $filename .= '.pdf';
    } elseif ($mime === 'image/jpeg') {
        $filename .= '.jpg';
    } elseif ($mime === 'image/png') {
        $filename .= '.png';
    } elseif ($mime === 'application/msword') {
        $filename .= '.doc';
    } elseif ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $filename .= '.docx';
    }

    return new Response($data, 200, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
        'Content-Length' => strlen($data)
    ]);
}
//update candidature par le RH 
// ===================== MISE À JOUR D’UNE CANDIDATURE =====================
#[Route('/candidacy/{id}/update', name: 'candidacy_update', methods: ['POST'])]
public function updateCandidacy(Candidacy $candidacy, Request $request): Response
{
    // Récupérer les données du formulaire
    $status = $request->request->get('status');
    $recruiterNote = $request->request->get('recruiter_note');
    $interviewDateStr = $request->request->get('interview_date');

    // Mettre à jour le statut
    if ($status) {
        $candidacy->setStatus($status);
    }

    // Mettre à jour la note RH
    $candidacy->setRecruiterNote($recruiterNote ?: null);

    // Mettre à jour la date d'entretien
    if ($interviewDateStr) {
        try {
            $interviewDate = new \DateTime($interviewDateStr);
            $candidacy->setInterviewDate($interviewDate);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Date d’entretien invalide.');
            return $this->redirectToRoute('candidacy_show', ['id' => $candidacy->getId()]);
        }
    } else {
        $candidacy->setInterviewDate(null);
    }

    // Enregistrer dans la base
    $this->entityManager->persist($candidacy);
    $this->entityManager->flush();

    $this->addFlash('success', 'Candidature mise à jour avec succès.');

    return $this->redirectToRoute('candidacy_show', ['id' => $candidacy->getId()]);
}



}
