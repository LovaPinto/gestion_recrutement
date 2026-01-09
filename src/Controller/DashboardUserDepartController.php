<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\Users;
use App\Entity\Company;
use App\Repository\JobOfferRepository;
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
        JobOfferRepository $jobOfferRepository
    ): Response
    {
        /* ================== SÉCURITÉ ================== */
        $companySession = $session->get('company');
        if (!$companySession) {
            $this->addFlash('error', 'Vous devez être connecté à une entreprise.');
            return $this->redirectToRoute('loginCompany');
        }

        $userId = $session->get('user_id');
        $roleId = $session->get('role_id'); // 1 = RH | 2 = Manager

        if (!$userId || !in_array($roleId, [1, 2])) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('login_department');
        }

        $user = $em->getRepository(Users::class)->find($userId);
        $company = $em->getRepository(Company::class)->find($companySession['id']);

        /* ================== NOMBRE TOTAL OFFRES ROLE ================== */
        $offersCount = $jobOfferRepository->count([
            'company' => $company,
            'roleId'  => $roleId
        ]);

        /* ================== NOMBRE PAR STATUT ================== */
        $statuses = ['en attente', 'publiée', 'déjà prise'];
        $offersByStatus = [];

        foreach ($statuses as $status) {
            $offersByStatus[$status] = $jobOfferRepository->count([
                'company' => $company,
                'roleId' => $roleId,
                'status' => $status
            ]);
        }

        /* ================== MOIS SÉLECTIONNÉ ================== */
        $monthParam = $request->query->get('month');
        if ($monthParam) {
            $monthStart = new \DateTime($monthParam . '-01');
        } else {
            $monthStart = new \DateTime('first day of this month');
        }
        $monthEnd = clone $monthStart;
        $monthEnd->modify('last day of this month 23:59:59');

        /* ================== OFFRES PAR JOUR POUR CHAQUE ROLE ================== */
        $qb = $jobOfferRepository->createQueryBuilder('o')
            ->select('o.roleId, o.dateCreation, COUNT(o.id) as count')
            ->where('o.company = :company')
            ->andWhere('o.dateCreation BETWEEN :start AND :end')
            ->groupBy('o.roleId, o.dateCreation')
            ->orderBy('o.dateCreation', 'ASC')
            ->setParameter('company', $company)
            ->setParameter('start', $monthStart)
            ->setParameter('end', $monthEnd);

        $result = $qb->getQuery()->getResult();

        /* ================== Préparer les labels et valeurs ================== */
        $daysInMonth = (int)$monthStart->format('t');
        $labels = range(1, $daysInMonth);
        $rhValues = array_fill(0, $daysInMonth, 0);
        $managerValues = array_fill(0, $daysInMonth, 0);

        foreach ($result as $row) {
            $date = $row['dateCreation'];
            if (!$date instanceof \DateTimeInterface) {
                $date = new \DateTime($date);
            }
            $dayIndex = (int)$date->format('d') - 1; // index 0-based
            if ($row['roleId'] == 1) {
                $rhValues[$dayIndex] = (int)$row['count'];
            } elseif ($row['roleId'] == 2) {
                $managerValues[$dayIndex] = (int)$row['count'];
            }
        }

        return $this->render('department/dashboardUserDepart.html.twig', [
            'offersCount'    => $offersCount,
            'companyName'    => $company->getCompanyName(),
            'roleId'         => $roleId,
            'chartLabels'    => $labels,
            'chartRH'        => $rhValues,
            'chartManager'   => $managerValues,
            'selectedMonth'  => $monthStart->format('Y-m'),
            'offersByStatus' => $offersByStatus
        ]);
    }
}
