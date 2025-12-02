<?php

namespace App\Repository;

use App\Entity\JobOffer;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOffer>
 */
class JobOfferRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }
    
    public function findAllJob(): array{
        return $this->createQueryBuilder('j')
        ->innerJoin('j.company', 'cp')
        ->addSelect('cp')
        ->orderBy('j.dateCreation', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
    
    }

    public function insertJob(string $offerType,string $description,
    \DateTimeInterface $dateCreation,\DateTimeInterface $deadline,Company $company): void {
        $job = new JobOffer();
        $job->setOfferType($offerType);
        $job->setDescription($description);
        $job->setDateCreation($dateCreation);
        $job->setDeadline($deadline);
        $job->setCompany($company);
    
        $em = $this->getEntityManager();
        $em->persist($job);
        $em->flush();
    }
    

    
    //    /**
    //     * @return JobOffer[] Returns an array of JobOffer objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?JobOffer
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
