<?php
namespace App\Controller;

use App\Entity\Candidate;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/candidate')]
final class CandidateController extends AbstractController
{
    // Portail candidat
    #[Route('/jobtracker', name: 'jobtracker')]
    public function showPortail(): Response
    {
        return $this->render('candidate/Portail_candidate.html.twig');
    }

    // Login candidat
    #[Route('/login', name: 'app_candidate_login')]
    public function login(): Response
    {
        return $this->render('candidate/login_candidate.html.twig');
    }

    // Dashboard candidat
    #[Route('/dashboard', name: 'app_candidate_dashboard')]
    // #[IsGranted('ROLE_CANDIDATE')]
    public function dashboard(CandidateRepository $candidateRepository): Response
    {
        $candidate = $candidateRepository->findOneBy(['user' => $this->getUser()]);

        return $this->render('candidate/dashboard.html.twig', [
            'candidate' => $candidate,
        ]);
    }

    // Liste des candidats
    #[Route('/', name: 'app_candidate_index', methods: ['GET'])]
    //#[IsGranted('ROLE_ADMIN')]
    public function index(CandidateRepository $candidateRepository): Response
    {
        return $this->render('candidate/index.html.twig', [
            'candidates' => $candidateRepository->findAll(),
        ]);
    }

    // Création d'un candidat
    #[Route('/new', name: 'app_candidate_new', methods: ['GET', 'POST'])]
    public function new (Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidate = new Candidate();
        $form      = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidate);
            $entityManager->flush();

            $this->addFlash('success', 'Candidature créée avec succès!');
            return $this->redirectToRoute('candidate_dashboard');
        }

        return $this->render('candidate/new.html.twig', [
            'candidate' => $candidate,
            'form'      => $form,
        ]);
    }

    // Affichage d'un candidat
    #[Route('/{id}', name: 'app_candidate_show', methods: ['GET'])]
    public function show(Candidate $candidate): Response
    {
        return $this->render('candidate/show.html.twig', [
            'candidate' => $candidate,
        ]);
    }

    // Édition d'un candidat
    #[Route('/{id}/edit', name: 'app_candidate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidate $candidate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Candidature mise à jour!');
            return $this->redirectToRoute('candidate_dashboard');
        }

        return $this->render('candidate/edit.html.twig', [
            'candidate' => $candidate,
            'form'      => $form,
        ]);
    }

    // Suppression d'un candidat
    #[Route('/{id}/delete', name: 'app_candidate_delete', methods: ['POST'])]
    public function delete(Request $request, Candidate $candidate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidate->getId(), $request->request->get('_token'))) {
            $entityManager->remove($candidate);
            $entityManager->flush();
            $this->addFlash('success', 'Candidature supprimée!');
        }

        return $this->redirectToRoute('candidate_dashboard');
    }
}
