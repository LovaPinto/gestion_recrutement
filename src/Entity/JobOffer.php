<?php

namespace App\Entity;

use App\Repository\JobOfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
class JobOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $offerType = null;

    #[ORM\Column(length: 50)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $deadline = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'jobOffers')]
    private ?Users $user = null;

    #[ORM\OneToMany(mappedBy: 'jobOffer', targetEntity: Candidacy::class, orphanRemoval: true)]
    private Collection $candidacies;

    #[ORM\ManyToMany(targetEntity: Candidate::class, inversedBy: 'jobOffers')]
    #[ORM\JoinTable(name: 'job_offer_candidate')]
    private Collection $candidates;

    // =====================
    // Constructor
    // =====================
    public function __construct()
    {
        $this->candidacies = new ArrayCollection();
        $this->candidates = new ArrayCollection();
    }

    // =====================
    // Getters & Setters
    // =====================

    public function getId(): ?int
    {
        return $this->id;
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

    public function setUser(?Users $user): static
    {
        $this->user = $user;
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
            $candidacy->setJobOffer($this);
        }
        return $this;
    }

    public function removeCandidacy(Candidacy $candidacy): static
    {
        if ($this->candidacies->removeElement($candidacy)) {
            if ($candidacy->getJobOffer() === $this) {
                $candidacy->setJobOffer(null);
            }
        }
        return $this;
    }

    // =====================
    // Candidates Relation
    // =====================
    /**
     * @return Collection|Candidate[]
     */
    public function getCandidates(): Collection
    {
        return $this->candidates;
    }

    public function addCandidate(Candidate $candidate): static
    {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates->add($candidate);
            $candidate->addJobOffer($this);
        }
        return $this;
    }

    public function removeCandidate(Candidate $candidate): static
    {
        if ($this->candidates->removeElement($candidate)) {
            $candidate->removeJobOffer($this);
        }
        return $this;
    }
}
