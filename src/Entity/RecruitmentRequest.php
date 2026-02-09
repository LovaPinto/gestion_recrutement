<?php 
// src/Entity/RecruitmentRequest.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RecruitmentRequest
{
    public const STATUS_BROUILLON = 'brouillon';
    public const STATUS_ENVOYEE   = 'envoyee';
    public const STATUS_REFUSEE   = 'refusee';
    public const STATUS_VALIDEE   = 'validee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $poste;

    #[ORM\Column(length: 50)]
    private string $typeContrat;

    #[ORM\Column(type: 'text')]
    private string $justification;

    #[ORM\Column(type: 'integer')]
    private int $nombrePostes;

    #[ORM\Column(length: 50)]
    private string $experienceSouhaitee;

    #[ORM\Column(length: 50)]
    private string $status = self::STATUS_BROUILLON;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Users $manager;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Department $department;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // getters / setters …
}
