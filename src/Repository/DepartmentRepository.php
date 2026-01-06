<?php

namespace App\Repository;

use App\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

// ===================== LISTE DEPARTEMENTS POUR SELECT =====================
    public function findAllForSelect(): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, d.departmentName')
            ->groupBy('d.id, d.departmentName')
            ->orderBy('d.departmentName', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //select du nom de l'entreprise
    public function findAllDepartmentName(): array
    {
        return $this->createQueryBuilder('d')
            ->select('DISTINCT d.departmentName AS departmentName')
            ->orderBy('d.departmentName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
