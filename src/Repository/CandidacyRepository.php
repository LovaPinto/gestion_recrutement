<?php

namespace App\Repository;

use App\Entity\Candidacy;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidacyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidacy::class);
    }

    // Count par statut pour un utilisateur donné
    public function countByUserAndStatus(Users $user, string $status): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.user = :user')
            ->andWhere('c.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Méthodes pratiques pour chaque statut
    public function countPending(Users $user): int
    {
        return $this->countByUserAndStatus($user, 'En attente');
    }

    public function countAccepted(Users $user): int
    {
        return $this->countByUserAndStatus($user, 'Acceptée');
    }

    public function countRefused(Users $user): int
    {
        return $this->countByUserAndStatus($user, 'Refusée');
    }

    public function countInterviewInvited(Users $user): int
    {
        return $this->countByUserAndStatus($user, 'Invitée à un entretien');
    }
}
