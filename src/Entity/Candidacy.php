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

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::BLOB)]
    private mixed $cvPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $portfolioLink = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private mixed $attachement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recruiterNote = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $interviewDate = null;

    #[ORM\ManyToOne(inversedBy: 'candidacies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobOffer $jobOffer = null;

    // ================= NOUVELLE RELATION AVEC USERS =================
    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'candidacies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    // ================= GETTERS & SETTERS =================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
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

    public function getInterviewDate(): ?\DateTime
    {
        return $this->interviewDate;
    }

    public function setInterviewDate(?\DateTime $interviewDate): static
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

    // ================= GETTER & SETTER POUR USER =================
    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;
        return $this;
    }
}
