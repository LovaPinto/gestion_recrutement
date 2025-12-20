<?php

namespace App\Repository;

use App\Entity\JobOffer;
use App\Entity\Users;
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

 public function findAllJob(): array
{
    return $this->createQueryBuilder('j')
        // relation JobOffer → User
        ->innerJoin('j.user', 'u')
        ->addSelect('u')

        // relation User → Department
        ->innerJoin('u.department', 'd')
        ->addSelect('d')

        // relation Department → Company
        ->innerJoin('d.company', 'c')
        ->addSelect('c')


        ->orderBy('j.dateCreation', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
}
//maka a partir de offerType
 public function findAllJobByOfferType($offerType): array
{
    return $this->createQueryBuilder('j')
        // relation JobOffer → User
        ->innerJoin('j.user', 'u')
        ->addSelect('u')

        // relation User → Department
        ->innerJoin('u.department', 'd')
        ->addSelect('d')

        // relation Department → Company
        ->innerJoin('d.company', 'c')
        ->addSelect('c')

        ->andWhere('j.offerType = :offerType')
        ->setParameter('offerType',$offerType)

        ->orderBy('j.dateCreation', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
}




  public function insertJob(
    string $offerType,
    string $description,
    \DateTimeInterface $dateCreation,
    \DateTimeInterface $deadline,
    Users $user
): void {
    $job = new JobOffer();
    $job->setOfferType($offerType);
    $job->setDescription($description);
    $job->setDateCreation($dateCreation);
    $job->setDeadline($deadline);
    $job->setUser($user);

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
