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
    /* ================= STATUTS ================= */
    public const STATUS_PUBLIEE   = 'publiee';
    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_PRISE     = 'prise';
    public const STATUS_SUPPRIMEE = 'supprimee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    private ?string $offerType = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $deadline = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'jobOffers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Company::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: Department::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Department $department = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $job_skills = [];

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $experience_level = null;

    /**
     * @var Collection<int, Candidacy>
     */
    #[ORM\OneToMany(mappedBy: 'jobOffer', targetEntity: Candidacy::class, orphanRemoval: true)]
    private Collection $candidacies;

    #[ORM\ManyToMany(targetEntity: Candidate::class, inversedBy: 'jobOffers')]
    #[ORM\JoinTable(name: 'job_offer_candidate')]
    private Collection $candidates;

    public function __construct()
    {
        $this->candidacies = new ArrayCollection();
        $this->candidates = new ArrayCollection();
        $this->job_skills = [];
        $this->status = self::STATUS_EN_ATTENTE; // initialisation par défaut
    }

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

    public function setDeadline(?\DateTime $deadline): static
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

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
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

    public function getJobSkills(): array
    {
        return $this->job_skills ?? [];
    }

    public function setJobSkills(?array $job_skills): static
    {
        $this->job_skills = $job_skills ?? [];
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $allowedStatuses = [
            self::STATUS_PUBLIEE,
            self::STATUS_EN_ATTENTE,
            self::STATUS_PRISE,
            self::STATUS_SUPPRIMEE,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException('Statut invalide pour une offre d\'emploi');
        }

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

    // ===================== Candidacies Relation =====================
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

    // ===================== Candidates Relation =====================
    /**
     * @return Collection|Candidate[]
     */
    public function getCandidates(): Collection
    {
        return $this->candidates;
    }

}
