<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\Candidacy;
use App\Entity\Users;
use App\Repository\JobOfferRepository;
use App\Repository\CandidacyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class DashboardManagerController extends AbstractController
{
    #[Route('/dashboard/Manager', name: 'dashboardManager')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        JobOfferRepository $jobOfferRepository,
        CandidacyRepository $candidacyRepository
    ): Response {

        /* ===================== USER CONNECTÉ ===================== */
        $userId = $request->getSession()->get('user_id');

        if (!$userId) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        /** @var Users $manager */
        $manager = $em->getRepository(Users::class)->find($userId);

        if (!$manager || !$manager->isManager()) {
            throw $this->createAccessDeniedException('Accès réservé au Manager.');
        }

        $department = $manager->getDepartment();
        $company    = $department?->getCompany();

        if (!$department || !$company) {
            throw $this->createNotFoundException('Département ou entreprise introuvable.');
        }

        /* ===================== OFFRES DU DÉPARTEMENT ===================== */
        $jobOffers = $jobOfferRepository->findBy(
            [
                'company'    => $company,
                'department' => $department
            ],
            ['dateCreation' => 'DESC']
        );

        /* ===================== STATISTIQUES ===================== */

        // Total
        $totalOffers = count($jobOffers);

        // En attente
        $pendingOffers = $jobOfferRepository->count([
            'company'    => $company,
            'department' => $department,
            'status'     => JobOffer::STATUS_EN_ATTENTE
        ]);

        // Publiées
        $publishedOffers = $jobOfferRepository->count([
            'company'    => $company,
            'department' => $department,
            'status'     => JobOffer::STATUS_PUBLIEE
        ]);

        // Prises
        $takenOffers = $jobOfferRepository->count([
            'company'    => $company,
            'department' => $department,
            'status'     => JobOffer::STATUS_PRISE
        ]);

        // 📩 Candidatures invitées à un entretien
        $interviewCount = $candidacyRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.jobOffer', 'o')
            ->where('o.company = :company')
            ->andWhere('o.department = :department')
            ->andWhere('c.status = :status')
            ->setParameter('company', $company)
            ->setParameter('department', $department)
            ->setParameter('status', Candidacy::STATUS_INTERVIEW)
            ->getQuery()
            ->getSingleScalarResult();

        /* ===================== RENDU ===================== */
        return $this->render('department/dashboadManagerDepart.html.twig', [
            'manager'          => $manager,
            'department'       => $department,

            'totalOffers'      => $totalOffers,
            'pendingOffers'    => $pendingOffers,
            'publishedOffers'  => $publishedOffers,
            'takenOffers'      => $takenOffers,
            'interviewCount'   => $interviewCount,

            'jobOffers'        => $jobOffers,
        ]);
    }
}
