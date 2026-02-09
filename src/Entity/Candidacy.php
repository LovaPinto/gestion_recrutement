<?php

namespace App\Entity;

use App\Repository\CandidacyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;

#[ORM\Entity(repositoryClass: CandidacyRepository::class)]
class Candidacy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCandidacy = null;

    /* =====================================================
     * STATUTS — VALEURS EXACTES DE LA BASE DE DONNÉES
     * ===================================================== */
    public const STATUS_PENDING   = 'en attente';
    public const STATUS_INTERVIEW = 'invité à un entretien';
    public const STATUS_ACCEPTED  = 'acceptée';
        public const STATUS_REFUSED   = 'refusée';
    

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_INTERVIEW,
        self::STATUS_ACCEPTED,
        self::STATUS_REFUSED,
    ];

    #[ORM\Column(length: 50, options: ['default' => 'en attente'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::BLOB)]
    private mixed $cvPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $portfolioLink = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private mixed $attachement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recruiterNote = null;

#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
private ?\DateTimeInterface $interviewDate = null;


    #[ORM\ManyToOne(inversedBy: 'candidacies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobOffer $jobOffer = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'candidacies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    /* ================== ATS ================== */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $atsScore = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $cvMimeType = null;

    /* ================= GETTERS & SETTERS ================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCandidacy(): ?\DateTime
    {
        return $this->dateCandidacy;
    }

    public function setDateCandidacy(\DateTime $dateCandidacy): static
    {
        $this->dateCandidacy = $dateCandidacy;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * ✅ CORRECTION CRITIQUE
     * - n’écrase PLUS les statuts valides
     * - respecte EXACTEMENT les valeurs de la base
     */
    public function setStatus(string $status): static
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Statut invalide : ' . $status);
        }

        $this->status = $status;
        return $this;
    }

    public function getCvPath(): mixed
    {
        return $this->cvPath;
    }

    public function setCvPath(mixed $cvPath): static
    {
        $this->cvPath = $cvPath;
        return $this;
    }

    public function getPortfolioLink(): ?string
    {
        return $this->portfolioLink;
    }

    public function setPortfolioLink(?string $portfolioLink): static
    {
        $this->portfolioLink = $portfolioLink;
        return $this;
    }

    public function getAttachement(): mixed
    {
        return $this->attachement;
    }

    public function setAttachement(mixed $attachement): static
    {
        $this->attachement = $attachement;
        return $this;
    }

    public function getRecruiterNote(): ?string
    {
        return $this->recruiterNote;
    }

    public function setRecruiterNote(?string $recruiterNote): static
    {
        $this->recruiterNote = $recruiterNote;
        return $this;
    }

 public function getInterviewDate(): ?\DateTimeInterface
{
    return $this->interviewDate;
}


 public function setInterviewDate(?\DateTimeInterface $interviewDate): static
{
    $this->interviewDate = $interviewDate;
    return $this;
}


    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): static
    {
        $this->jobOffer = $jobOffer;
        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;
        return $this;
    }

    public function getAtsScore(): ?float
    {
        return $this->atsScore;
    }

    public function setAtsScore(?float $score): static
    {
        $this->atsScore = $score;
        return $this;
    }
    public function getCvMimeType(): ?string
    {
        return $this->cvMimeType;
    }
}
