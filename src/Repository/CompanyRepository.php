<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }
    public function insertCompany($companyName, $email, $password, $description, $role_id)
    {
        // Créer une nouvelle instance de Company
        $company = new Company();
        // Ici on appelle bien la méthode getEntityManager()
        $role = $this->getEntityManager()
            ->getRepository(Role::class)
            ->find($role_id);

        $company->setCompanyName($companyName);
        $company->setEmail($email);
        $company->setPassword($password);
        $company->setDescription($description);
        $company->setRole($role);
        $company->setDateCreation(new \DateTime());
        $company->setStatus(true);
        // Persister et sauvegarder en base
        $em = $this->getEntityManager();
        $em->persist($company);
        $em->flush();

        return $company;
    }
    //nombre company creer
    public function countCompanies(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countCompanieThisMonth(): int
    {
        $start = new \DateTime('first day of this month 00:00:00');
        $end   = new \DateTime('last day of this month 23:59:59');

        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.dateCreation BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function findAllCompany(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateCreation', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
    public function countCompanyByStatus($status): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.status=:stat')
            ->setParameter('stat', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
