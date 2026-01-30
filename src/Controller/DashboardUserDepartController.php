<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\Candidacy;
use App\Entity\Users;
use App\Entity\Company;
use App\Repository\JobOfferRepository;
use App\Repository\CandidacyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class DashboardUserDepartController extends AbstractController
{
    #[Route('/department/dashboard', name: 'dashboard_user_depart')]
    public function dashboard(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        JobOfferRepository $jobOfferRepository,
        CandidacyRepository $candidacyRepository
    ): Response
    {
        /* ================== SÉCURITÉ ================== */
        $companySession = $session->get('company');
        $userId         = $session->get('user_id');
        $roleId         = $session->get('role_id'); // 1 = RH | 2 = Manager

        if (!$companySession || !$userId || !in_array($roleId, [1, 2])) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('loginCompany');
        }

        $user    = $em->getRepository(Users::class)->find($userId);
        $company = $em->getRepository(Company::class)->find($companySession['id']);

        if (!$company) {
            throw $this->createNotFoundException('Entreprise introuvable.');
        }

        // Département de l’utilisateur (peut être null)
        $department = $user->getDepartment();

        /* ================== OFFRES ================== */
        $criteria = ['company' => $company];
        if ($department) {
            $criteria['department'] = $department;
        }

        $jobOffers = $jobOfferRepository->findBy($criteria, ['dateCreation' => 'ASC']);

        // Comptage par statut
        $offersByStatus = [
            'en attente'  => $jobOfferRepository->count(array_merge($criteria, ['status' => JobOffer::STATUS_EN_ATTENTE])),
            'publiée'     => $jobOfferRepository->count(array_merge($criteria, ['status' => JobOffer::STATUS_PUBLIEE])),
            'déjà prise'  => $jobOfferRepository->count(array_merge($criteria, ['status' => JobOffer::STATUS_PRISE])),
            'refusée'     => $jobOfferRepository->count(array_merge($criteria, ['status' => JobOffer::STATUS_REFUSEE])),
        ];

        $offersCount = array_sum($offersByStatus);

        /* ================== OFFRES PAR JOUR POUR LE CHART ================== */
        $monthParam = $request->query->get('month');
        $monthStart = $monthParam ? new \DateTime($monthParam . '-01') : new \DateTime('first day of this month');
        $monthEnd   = (clone $monthStart)->modify('last day of this month 23:59:59');

        $qb = $jobOfferRepository->createQueryBuilder('o')
            ->select('o.dateCreation, COUNT(o.id) as count')
            ->where('o.company = :company')
            ->setParameter('company', $company);

        if ($department) {
            $qb->andWhere('o.department = :department')
               ->setParameter('department', $department);
        }

        $qb->andWhere('o.status = :status')
           ->andWhere('o.dateCreation BETWEEN :start AND :end')
           ->setParameter('status', JobOffer::STATUS_PUBLIEE)
           ->setParameter('start', $monthStart)
           ->setParameter('end', $monthEnd)
           ->groupBy('o.dateCreation')
           ->orderBy('o.dateCreation', 'ASC');

        $result = $qb->getQuery()->getResult();

        $daysInMonth = (int)$monthStart->format('t');
        $chartLabels = range(1, $daysInMonth);
        $chartValues = array_fill(0, $daysInMonth, 0);

        foreach ($result as $row) {
            $date = $row['dateCreation'] instanceof \DateTimeInterface ? $row['dateCreation'] : new \DateTime($row['dateCreation']);
            $dayIndex = (int)$date->format('d') - 1;
            $chartValues[$dayIndex] = (int)$row['count'];
        }

        /* ================== MESSAGE PROCHAIN ENTRETIEN ================== */
        $today     = new \DateTime();
        $threeDays = (clone $today)->modify('+3 days');

        $nextInterview = $candidacyRepository->createQueryBuilder('c')
            ->join('c.jobOffer', 'o')
            ->where('o.company = :company')
            ->setParameter('company', $company);

        if ($department) {
            $nextInterview->andWhere('o.department = :department')
                          ->setParameter('department', $department);
        }

        $nextInterview->andWhere('c.status = :status')
                      ->andWhere('c.interviewDate BETWEEN :today AND :threeDays')
                      ->setParameter('status', Candidacy::STATUS_INTERVIEW)
                      ->setParameter('today', $today)
                      ->setParameter('threeDays', $threeDays)
                      ->orderBy('c.interviewDate', 'ASC')
                      ->setMaxResults(1);

        $nextInterview = $nextInterview->getQuery()->getOneOrNullResult();

        $interviewMessage = $nextInterview
            ? 'Vous avez un entretien prévu le ' . $nextInterview->getInterviewDate()->format('d/m/Y H:i')
            : 'Vous n\'avez aucun entretien prévu pour l’instant.';

        /* ================== RENDU ================== */
        return $this->render('department/dashboardUserDepart.html.twig', [
            'user'             => $user,
            'companyName'      => $company->getCompanyName(),
            'department'       => $department,
            'offersByStatus'   => $offersByStatus,
             'roleId'           => $roleId,  // <-- Ajouté ici
            'offersCount'      => $offersCount,
            'chartLabels'      => $chartLabels,
            'chartValues'      => $chartValues,
            'selectedMonth'    => $monthStart->format('Y-m'),
            'interviewMessage' => $interviewMessage,
        ]);
    }
}
