<?php

namespace App\Repository;

use App\Entity\Candidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
    // CandidateRepository.php
public function findStats($user): array
{
  
        $qb = $this->createQueryBuilder('c')
            ->select('c.id, c.firstName, c.lastName, c.email, u.id AS userId, u.email AS userEmail')
            ->innerJoin('c.user', 'u') // Assure-toi que Candidate a un champ "user" ManyToOne vers Users
            ->where('u.email = :email')
            ->setParameter('email', $user->getEmail());

        $result = $qb->getQuery()->getArrayResult();

        // Si tu veux juste un candidat, retourne le premier élément ou null
        return $result[0] ?? null;
}

}
