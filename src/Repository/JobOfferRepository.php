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
    public function findAllCompanyName(): array
    {
        return $this->createQueryBuilder('j')
            ->select('c.id, c.companyName')
            ->join('j.company', 'c')
            ->groupBy('c.id, c.companyName')
            ->orderBy('c.companyName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /* ===================== DÉPARTEMENTS SANS DOUBLONS ===================== */
    public function findAllDepartmentName(): array
    {
        return $this->createQueryBuilder('j')
            ->select('d.id, d.departmentName')
            ->join('j.department', 'd')
            ->groupBy('d.id, d.departmentName')
            ->orderBy('d.departmentName', 'ASC')
            ->getQuery()
            ->getResult();
    }

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
    public function findAllByFilter($keyword, $companyId, $departmentId, $offerType)
    {
        $qb = $this->createQueryBuilder('j')
            ->select('j.id, j.title, j.offerType, c.companyName, d.departmentName')
            ->join('j.company', 'c')
            ->join('j.department', 'd');

        if (!empty($keyword)) {
            $qb->andWhere('j.title LIKE :keyword')
               ->setParameter('keyword', '%' . $keyword . '%');
        }

        if (!empty($companyId)) {
            $qb->andWhere('c.id = :companyId')
               ->setParameter('companyId', $companyId);
        }

        if (!empty($departmentId)) {
            $qb->andWhere('d.id = :departmentId')
               ->setParameter('departmentId', $departmentId);
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


     public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('j')
            ->orderBy('j.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
