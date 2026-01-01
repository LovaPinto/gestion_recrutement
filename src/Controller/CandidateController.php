<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Users;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use App\Repository\CandidacyRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


#[Route('/candidate')]
final class CandidateController extends AbstractController
{

    #[Route('/jobtracker', name: 'app_candidate_jobtracker', methods: ['GET'])]
    public function showPortail(): Response
    {
        return $this->render('candidate/Portail_candidate.html.twig');
    }


    #[Route('/dashboard', name: 'app_candidate_dashboard')]
    public function dashboard(
        CandidateRepository $candidateRepository
    ): Response {
        $candidate = $candidateRepository->findOneBy([
            'user' => $this->getUser()
        ]);

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

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_candidate_dashboard');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login_candidate.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
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
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide');
        }

        return $this->redirectToRoute('app_candidate_index');
    }
}
