<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\Candidacy;
use App\Entity\Department;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\JobOfferRepository;
use App\Repository\CandidacyRepository;
use App\Repository\DepartmentRepository;

class CompanyDashboardController extends AbstractController
{
    #[Route('/company/dashboard', name: 'company_dashboard')]
    public function index(
        EntityManagerInterface $em,
        JobOfferRepository $jobOfferRepository,
        CandidacyRepository $candidacyRepository,
        DepartmentRepository $departmentRepository,
        Request $request
    ): Response {

        // 🔹 Société connectée
        $companySession = $request->getSession()->get('company');
        if (!$companySession) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('loginCompany');
        }
        $company = $em->getRepository('App\Entity\Company')->find($companySession['id']);
        if (!$company) {
            $this->addFlash('error', 'Société introuvable.');
            return $this->redirectToRoute('loginCompany');
        }

        // 🔹 Offres de la société
        $jobOffers = $jobOfferRepository->findBy(['company' => $company]);

        // ================= Stats Candidatures =================
        $totalCandidatures = $enAttente = $refusees = $acceptees = 0;

        foreach ($jobOffers as $offer) {
            foreach ($offer->getCandidacies() as $c) {
                $totalCandidatures++;
                switch ($c->getStatus()) {
                    case Candidacy::STATUS_PENDING: $enAttente++; break;
                    case Candidacy::STATUS_REFUSED: $refusees++; break;
                    case Candidacy::STATUS_ACCEPTED: $acceptees++; break;
                }
            }
        }

        $candidaturesByStatus = [
            'En attente' => $enAttente,
            'Acceptées' => $acceptees,
            'Refusées' => $refusees,
        ];

        // 🔹 Préparer labels et values pour Chart.js
        $statusLabels = array_keys($candidaturesByStatus);
        $statusValues = array_values($candidaturesByStatus);

        // ================= Offres par département =================
        $departments = $departmentRepository->findBy(['company' => $company]);
        $offersByDept = [];
        foreach ($departments as $dept) {
            $offersByDept[$dept->getDepartmentName()] = 0;
            foreach ($jobOffers as $offer) {
                if ($offer->getDepartment() && $offer->getDepartment()->getId() === $dept->getId()) {
                    $offersByDept[$dept->getDepartmentName()]++;
                }
            }
        }

        // ================= Candidatures sur 6 derniers mois =================
        $months = [];
        $candidatures6mois = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $monthLabel = $date->format('M Y');
            $months[] = $monthLabel;

            $start = (clone $date)->modify('first day of this month')->setTime(0,0,0);
            $end = (clone $date)->modify('last day of this month')->setTime(23,59,59);

            $count = 0;
            foreach ($jobOffers as $offer) {
                foreach ($offer->getCandidacies() as $c) {
                    if ($c->getDateCandidacy() >= $start && $c->getDateCandidacy() <= $end) {
                        $count++;
                    }
                }
            }
            $candidatures6mois[] = $count;
        }

        $candidaturesByMonth = [];
        foreach ($months as $index => $month) {
            $candidaturesByMonth[] = [
                'month' => $month,
                'count' => $candidatures6mois[$index] ?? 0,
            ];
        }

        // 🔹 Labels et values pour Chart.js
        $monthlyLabels = $months;
        $monthlyValues = $candidatures6mois;

        // ================= Délai moyen par département =================
        $avgDelayByDept = [];
        foreach ($departments as $dept) {
            $totalDays = 0;
            $count = 0;
            foreach ($jobOffers as $offer) {
                if ($offer->getDepartment() && $offer->getDepartment()->getId() === $dept->getId()) {
                    foreach ($offer->getCandidacies() as $c) {
                        if ($c->getStatus() === Candidacy::STATUS_ACCEPTED) {
                            $diff = $c->getDateCandidacy()->diff($c->getInterviewDate() ?? new \DateTime());
                            $totalDays += $diff->days;
                            $count++;
                        }
                    }
                }
            }
            $avgDelayByDept[$dept->getDepartmentName()] = $count ? round($totalDays / $count, 1) : 0;
        }

        // 🔹 Render
        return $this->render('company/dashboard.html.twig', [
            'company' => $company,
            'totalCandidatures' => $totalCandidatures,
            'enAttente' => $enAttente,
            'refusees' => $refusees,
            'acceptees' => $acceptees,
            'candidaturesByStatus' => $candidaturesByStatus,
            'statusLabels' => $statusLabels,
            'statusValues' => $statusValues,
            'offersByDept' => $offersByDept,
            'candidaturesByMonth' => $candidaturesByMonth,
            'monthlyLabels' => $monthlyLabels,
            'monthlyValues' => $monthlyValues,
            'avgDelayByDept' => $avgDelayByDept,
        ]);
    }
}
