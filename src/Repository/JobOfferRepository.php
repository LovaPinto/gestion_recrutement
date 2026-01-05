<?php

namespace App\Repository;

use App\Entity\JobOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOffer>
 */
class JobOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }

    // Méthode pour récupérer les noms des entreprises
    public function findAllCompanyName(): array
    {
        // Récupère à la fois l'ID et le nom de l'entreprise
        return $this->createQueryBuilder('j')
            ->select(' DISTINCT c.id, c.companyName')  // Sélectionne l'ID et le nom de l'entreprise
            ->join('j.company', 'c')        // Jointure avec l'entité 'Company'
            ->getQuery()
            ->getResult();
    }
    public function findAllDepartmentName(): array
    {
        // Récupère à la fois l'ID et le nom du département
        return $this->createQueryBuilder('j')
            ->select(' DISTINCT d.id, d.departmentName')  // Sélectionne l'ID et le nom du département
            ->join('j.department', 'd')        // Jointure avec l'entité 'Department'
            ->getQuery()
            ->getResult();
    }

    // Méthode pour récupérer les types d'offres distincts
    public function findAllOfferTypes(): array
    {
        return $this->createQueryBuilder('j')
            ->select('DISTINCT j.offerType')
            ->getQuery()
            ->getResult();
    }

    public function findAllJobOffer()
    {
        return $this->createQueryBuilder('j')
            ->select('j.id, j.title, j.offerType, c.companyName, d.departmentName')
            ->join('j.company', 'c')
            ->join('j.department', 'd')
            ->getQuery()
            ->getResult();
    }

    public function findAllByFilter($keyword, $companyId, $departmentId, $offerType)
    {
        $qb = $this->createQueryBuilder('j')
            ->select('j.id', 'j.title', 'j.offerType', 'c.companyName', 'd.departmentName')
            ->join('j.company', 'c')
            ->join('j.department', 'd');

        // Filtrage par mot-clé dans le titre
        if ($keyword) {
            $qb->andWhere('j.title LIKE :keyword')
                ->setParameter('keyword', '%' . $keyword . '%');
        }

        // Filtrage par ID de l'entreprise
        if ($companyId) {
            $qb->andWhere('c.id = :companyId')
                ->setParameter('companyId', $companyId);
        }

        // Filtrage par ID du département
        if ($departmentId) {
            $qb->andWhere('d.id = :departmentId')
                ->setParameter('departmentId', $departmentId);
        }
        // Filtrage par type d'offre
        if ($offerType) {
            $qb->andWhere('j.offerType = :offerType')
                ->setParameter('offerType', $offerType);
        }

        // Exécution de la requête et retour des résultats
        return $qb->getQuery()->getResult();
    }


    //afficher toutes les offres d'emploi
    public function findAll(): array
    {
        return $this->createQueryBuilder('j')
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
