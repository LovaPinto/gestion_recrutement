<?php

namespace App\Entity;

use App\Entity\Candidate;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    private ?string $lastName = null;

    #[ORM\Column(length: 50)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    private ?Role $role = null;

    #[ORM\ManyToOne(targetEntity: Department::class)]
    private ?Department $department = null;
    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Candidate::class, cascade: ['persist', 'remove'])]
    private ?Candidate $candidate = null;


    // =====================
    // Relations
    // =====================

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: JobOffer::class, orphanRemoval: true)]
    private Collection $jobOffers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Candidacy::class)]
    private Collection $candidacies;

    // =====================
    // Constructor
    // =====================
    public function __construct()
    {
        $this->jobOffers = new ArrayCollection();
        $this->candidacies = new ArrayCollection();
    }

    // =====================
    // Getters & Setters
    // =====================

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): self
    {
        $this->candidate = $candidate;
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;
        return $this;
    }

    // =====================
    // JobOffers Relation
    // =====================
    /**
     * @return Collection|JobOffer[]
     */
    public function getJobOffers(): Collection
    {
        return $this->jobOffers;
    }

    public function addJobOffer(JobOffer $jobOffer): static
    {
        if (!$this->jobOffers->contains($jobOffer)) {
            $this->jobOffers->add($jobOffer);
            $jobOffer->setUser($this);
        }
        return $this;
    }

    public function removeJobOffer(JobOffer $jobOffer): static
    {
        if ($this->jobOffers->removeElement($jobOffer)) {
            if ($jobOffer->getUser() === $this) {
                $jobOffer->setUser(null);
            }
        }
        return $this;
    }

    // =====================
    // Candidacies Relation
    // =====================
    /**
     * @return Collection|Candidacy[]
     */
    public function getCandidacies(): Collection
    {
        return $this->candidacies;
    }

    public function addCandidacy(Candidacy $candidacy): static
    {
        if (!$this->candidacies->contains($candidacy)) {
            $this->candidacies->add($candidacy);
            $candidacy->setUser($this);
        }
        return $this;
    }

    public function removeCandidacy(Candidacy $candidacy): static
    {
        if ($this->candidacies->removeElement($candidacy)) {
            if ($candidacy->getUser() === $this) {
                $candidacy->setUser(null);
            }
        }
        return $this;
    }
}
