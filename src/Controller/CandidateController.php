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
    // Route pour le portail candidat (toujours en premier)
    #[Route('/jobtracker', name: 'jobtracker')]
    public function showPortail(): Response
    {
        return $this->render('candidate/Portail_candidate.html.twig');
    }
    // login cadidat
    #[Route('/login', name: 'app_candidate_login')]
    public function login(): Response
    {
        return $this->render('candidate/login_candidate.html.twig');
    }
    //root du post login 



    // CRUD classique
    #[Route(name: 'app_candidate_index', methods: ['GET'])]
    public function index(CandidateRepository $candidateRepository): Response
    {
        return $this->render('candidate/index.html.twig', [
            'candidates' => $candidateRepository->findAll(),
        ]);
    }
    #[Route('/candidate/dashboard', name: 'candidate_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('candidate/dashboard_candidate.html.twig');
    }




    #[Route('/new', name: 'app_candidate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidate = new Candidate();
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidate);
            $entityManager->flush();

            return $this->redirectToRoute('app_candidate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidate/new.html.twig', [
            'candidate' => $candidate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidate_show', methods: ['GET'])]
    public function show(Candidate $candidate): Response
    {
        return $this->render('candidate/show.html.twig', [
            'candidate' => $candidate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidate $candidate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_candidate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidate/edit.html.twig', [
            'candidate' => $candidate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidate_delete', methods: ['POST'])]
    public function delete(Request $request, Candidate $candidate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidate->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidate_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/candidate_profil', name: 'app_candidate_add', methods: ['POST'])]
    public function AddCandidate(Request $request, Candidate $candidate, CandidateRepository $candidateRepository): Response
    {
        $firstname = $request->request->get('first_name');
        $lastname = $request->request->get('last_name');
        $email = $request->request->get('email');
        $adress = $request->request->get('adress');
        $linkedIn = $request->request->get('linkedIn');
        $identityCard = $request->request->get('identityCard');
        $city = $request->request->get('city');
        $postal_code = $request->request->get('postal_code');
        $country = $request->request->get('country');
        $gender = $request->request->get('gender');
        $nationality = $request->request->get('nationality');
        $matrimonial_situation = $request->request->get('matrimonial_situation');
        $actual_status = $request->request->get('actual_status');




        $erreur = null;

        if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)) {

            $candidateRepository->saveUser($firstname, $lastname, $email, $password);

            return $this->redirectToRoute('app_user_login');
        }

        // Si erreur
        $erreur = "Un ou plusieurs champs sont vides";

        return $this->render('candidate/form_user.html.twig', [
            'error' => $erreur
        ]);
    }
}
