<?php

namespace App\Repository;

use App\Entity\Candidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use App\Entity\Users;

/**
 * @extends ServiceEntityRepository<Candidate>
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    //    /**
    //     * @return Candidate[] Returns an array of Candidate objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Candidate
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findStats(Users $user): array
    {
        $qb = $this->createQueryBuilder('c');

        try {
            // Exemple : compter le nombre de candidatures "Nouveau" pour ce candidat
            $newApplications = $qb->select('COUNT(c.id)')
                ->where('c.user = :user')
                ->andWhere('c.status = :status')
                ->setParameter('user', $user)
                ->setParameter('status', 'Nouveau')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $newApplications = 0;
        }

        // Tu peux ajouter d’autres stats ici, exemple "En attente", "Rejeté", etc.
        $stats = [
            'newApplications' => (int) $newApplications,
            // 'pendingApplications' => ...,
            // 'rejectedApplications' => ...,
        ];

        return $stats;
    }
}
