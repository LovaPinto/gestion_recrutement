<?php

namespace App\Entity;

use App\Repository\JobOfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
class JobOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // 🆕 Titre du job
    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    private ?string $offerType = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $deadline = null;

    // 👤 Créateur de l'offre
    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    // 🏢 Company

#[ORM\ManyToOne(targetEntity: Company::class)]
#[ORM\JoinColumn(nullable: false)]
private ?Company $company = null;


    // 🏬 Department
    #[ORM\ManyToOne(targetEntity: Department::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Department $department = null;

    #[ORM\Column]
    private array $job_skills = [];

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $experience_level = null;

    /* ================= GETTERS & SETTERS ================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getOfferType(): ?string
    {
        return $this->offerType;
    }

    public function setOfferType(string $offerType): static
    {
        $this->offerType = $offerType;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTime $deadline): static
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(Users $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(Department $department): static
    {
        $this->department = $department;
        return $this;
    }

    public function getJobSkills(): array
    {
        return $this->job_skills;
    }

    public function setJobSkills(array $job_skills): static
    {
        $this->job_skills = $job_skills;

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

    public function getExperienceLevel(): ?string
    {
        return $this->experience_level;
    }

    public function setExperienceLevel(string $experience_level): static
    {
        $this->experience_level = $experience_level;

        return $this;
    }
}
