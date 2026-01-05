<?php

namespace App\Repository;

use App\Entity\JobOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class JobOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }

    /* ===================== ENTREPRISES SANS DOUBLONS ===================== */
    

    /* ===================== TYPES D’OFFRES SANS DOUBLONS ===================== */
    public function findAllOfferTypes(): array
    {
        return $this->createQueryBuilder('j')
            ->select('DISTINCT j.offerType')
            ->orderBy('j.offerType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /* ===================== FILTRAGE DES OFFRES ===================== */
   // src/Repository/JobOfferRepository.php
public function findAllByFilter(
    ?string $keyword,
    ?string $companyName,
    ?string $departmentName,
    ?string $offerType
): array {
    $qb = $this->createQueryBuilder('j')
        ->select('j.id, j.title, j.offerType, c.companyName, d.departmentName')
        ->join('j.company', 'c')
        ->join('j.department', 'd');

    if (!empty($keyword)) {
        $qb->andWhere('j.title LIKE :keyword')
           ->setParameter('keyword', '%' . $keyword . '%');
    }

    if (!empty($companyName)) {
        $qb->andWhere('c.companyName = :companyName')
           ->setParameter('companyName', $companyName);
    }

    if (!empty($departmentName)) {
        $qb->andWhere('d.departmentName = :departmentName')
           ->setParameter('departmentName', $departmentName);
    }

    if (!empty($offerType)) {
        $qb->andWhere('j.offerType = :offerType')
           ->setParameter('offerType', $offerType);
    }

    return $qb
        ->orderBy('j.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}

    /* ===================== TOUTES LES OFFRES ===================== */
    public function findAll(): array
    {
        return $this->createQueryBuilder('j')
            ->orderBy('j.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
