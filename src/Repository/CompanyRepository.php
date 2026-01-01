<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /* ===================== LISTE ENTREPRISES POUR SELECT ===================== */
    public function findAllForSelect(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.companyName')
            ->groupBy('c.id, c.companyName')
            ->orderBy('c.companyName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
