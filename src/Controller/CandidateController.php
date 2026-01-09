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
use  App\Repository\CandidacyRepository;

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

#[Route('/profil', name: 'app_candidate_profil', methods: ['GET', 'POST'])]
public function profil(Request $request, EntityManagerInterface $em): Response
{
    $session = $request->getSession();
    $userId = $session->get('user_id');

    if (!$userId) {
        $this->addFlash('error', 'Utilisateur non connecté.');
        return $this->redirectToRoute('app_candidate_login');
    } else {
        $this->addFlash('info', 'Utilisateur connecté, ID=' . $userId);
    }

    // Récupération du user depuis la session
    $user = $em->getRepository(\App\Entity\Users::class)->find($userId);
    if (!$user) {
        $this->addFlash('error', 'Utilisateur introuvable en base.');
        return $this->redirectToRoute('app_candidate_login');
    } else {
        $this->addFlash('info', 'Utilisateur trouvé : ' . $user->getFirstName());
    }

    // Récupération du candidat lié à l'utilisateur
    $candidate = $em->getRepository(Candidate::class)->findOneBy(['user' => $user]);
    if (!$candidate) {
        $this->addFlash('error', 'Candidat introuvable pour cet utilisateur.');
        return $this->redirectToRoute('app_candidate_login');
    } else {
        $this->addFlash('info', 'Candidat trouvé : ' . $candidate->getNom());
    }

    // Création du formulaire
    $form = $this->createForm(CandidateType::class, $candidate);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        $this->addFlash('info', 'Formulaire soumis.');

        if ($form->isValid()) {
            $this->addFlash('success', 'Formulaire valide.');

            // Protection de l’email (clé unique)
            $candidate->setEmail($user->getEmail());

            try {
                $em->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_candidate_profil');
        } else {
            $this->addFlash('error', 'Formulaire invalide, vérifier les champs.');
        }
    } else {
        $this->addFlash('info', 'Formulaire non soumis.');
    }

    return $this->render('candidate/profil.html.twig', [
        'form' => $form->createView(),
        'candidate' => $candidate,
    ]);
}




    // Dashboard candidat
#[Route('/dashboard', name: 'app_candidate_dashboard')]
public function dashboard(
    Request $request, 
    EntityManagerInterface $em, 
    CandidacyRepository $candidacyRepository
): Response
{
    $userId = $request->getSession()->get('user_id');

    if (!$userId) {
        $this->addFlash('error', 'Vous devez être connecté.');
        return $this->redirectToRoute('candidat_login');
    }

    $user = $em->getRepository(\App\Entity\Users::class)->find($userId);
    if (!$user) {
        $this->addFlash('error', 'Utilisateur introuvable.');
        return $this->redirectToRoute('candidat_login');
    }

    // Counts des candidatures par statut pour l'utilisateur connecté
    $totalCandidatures = $candidacyRepository->countByUser($user);
    $enAttente = $candidacyRepository->countPending($user);
    $acceptees = $candidacyRepository->countAccepted($user);
    $refusees = $candidacyRepository->countRefused($user);
    $invited = $candidacyRepository->countInterviewInvited($user);

    // Liste de toutes les candidatures de l'utilisateur connecté
    $candidatures = $candidacyRepository->findBy(['user' => $user]);

    return $this->render('candidate/dashboard.html.twig', [
        'totalCandidatures' => $totalCandidatures,
        'enAttente' => $enAttente,
        'acceptees' => $acceptees,
        'refusees' => $refusees,
        'invited' => $invited,
        'candidatures' => $candidatures,
        'user' => $user,
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
