<?php

namespace App\Repository;

use App\Entity\NewUser;
use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Count;

use function Symfony\Component\Clock\now;

/**
 * @extends ServiceEntityRepository<NewUser>
 */
class NewUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewUser::class);
    }
      public function insertNewUser(NewUser $newUser): void {
        $newUser->setDateCreation(new \DateTime());
        $newUser->setUserStatus(true);
        $em = $this->getEntityManager();
        $em->persist($newUser);
        $em->flush();
    }
    public function countNewUser($role): int
    {
       return $this->createQueryBuilder('n')
           ->select('COUNT(n.id)')
           ->andWhere('n.role = :val')
           ->setParameter('val', $role)
           ->getQuery()
           ->getSingleScalarResult();
       ;
   }
    public function countNewUserParCompany($role,$company): int
    {
       return $this->createQueryBuilder('n')
           ->select('COUNT(n.id)')
           ->andWhere('n.role = :val')
           ->setParameter('val', $role)
            ->andWhere('n.company = :comp')
           ->setParameter('comp', $company)
           ->getQuery()
           ->getSingleScalarResult();
       ;
   }

   public function countNewUserThisMonth($role): int
    {
    $start = new \DateTime('first day of this month 00:00:00');
    $end   = new \DateTime('last day of this month 23:59:59');

    return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->andWhere('n.role = :val')
        ->andWhere('n.dateCreation BETWEEN :start AND :end')
        ->setParameter('val', $role)
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getQuery()
        ->getSingleScalarResult();
}

    //public function countNewUserByMonth($role): array
    //{
      // return $this->createQueryBuilder('n')
        //   ->select('COUNT(MONTH(n.dateCreation))')
          // ->andWhere('n.role = :val')
           //->setParameter('val', $role)
           //->getQuery()
           //->getResult()
       //;
   //}

//    /**
//     * @return NewUser[] Returns an array of NewUser objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NewUser
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
