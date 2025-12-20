<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/candidate')]
final class CandidateController extends AbstractController
{

    #[Route('/jobtracker', name: 'app_candidate_jobtracker', methods: ['GET'])]
    public function showPortail(): Response
    {
        return $this->render('candidate/Portail_candidate.html.twig');
    }

    #[Route('/login', name: 'app_candidate_login', methods: ['GET'])]
    public function login(): Response
    {
        return $this->render('candidate/login_candidate.html.twig');
    }


    #[Route('/dashboard', name: 'app_candidate_dashboard', methods: ['GET'])]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $candidate = $entityManager->getRepository(Candidate::class)->find(1);


        if (!$candidate) {
            throw $this->createNotFoundException('Candidat non trouvéec.');
        }

        return $this->render('candidate/dashboard.html.twig', [
            'candidate' => $candidate,
        ]);
    }


    #[Route('/profil/{id}', name: 'app_candidate_profil', methods: ['GET', 'POST'])]
    public function profil(
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): Response {

        $candidate = $entityManager->getRepository(Candidate::class)->find($id);

        if (!$candidate) {
            throw $this->createNotFoundException('Candidat non trouvé.');
        }

        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidate);
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_candidate_dashboard');
        }

        return $this->render('candidate/profil.html.twig', [
            'candidate' => $candidate,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/menu', name: 'app_candidate_menu', methods: ['GET'])]
    public function menu(): Response
    {
        return $this->render('candidate/menu.html.twig');
    }

    #[Route('', name: 'app_candidate_index', methods: ['GET'])]
    public function index(CandidateRepository $candidateRepository): Response
    {
        return $this->render('candidate/index.html.twig', [
            'candidates' => $candidateRepository->findAll(),
        ]);
    }

    // Création d'un candidat
    #[Route('/new', name: 'app_candidate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidate = new Candidate();
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidate);
            $entityManager->flush();

            $this->addFlash('success', 'Candidat créé avec succès');
            return $this->redirectToRoute('app_candidate_index');
        }

        return $this->render('candidate/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_candidate_show', methods: ['GET'])]
    public function show(Candidate $candidate): Response
    {
        return $this->render('candidate/show.html.twig', ['candidate' => $candidate]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_candidate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidate $candidate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Candidat mis à jour');
            return $this->redirectToRoute('app_candidate_index');
        }

        return $this->render('candidate/edit.html.twig', [
            'candidate' => $candidate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_candidate_delete', methods: ['POST'])]
    public function delete(Request $request, Candidate $candidate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidate->getId(), $request->request->get('_token'))) {
            $entityManager->remove($candidate);
            $entityManager->flush();
            $this->addFlash('success', 'Candidat supprimé');
        }

        return $this->redirectToRoute('app_candidate_index');
    }
}
