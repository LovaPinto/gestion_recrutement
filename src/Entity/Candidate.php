<?php

namespace App\Entity;

use App\Entity\Users;
use App\Repository\CandidateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: false, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    // =========================
    // Relation OneToOne avec Users
    // =========================
    #[ORM\OneToOne(inversedBy: 'candidate', targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Users $user = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: true)]
    private ?string $linkedin = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: true)]
    private ?string $facebook = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $nationalite = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private mixed $cvFile = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private mixed $lmFile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $password = null;

    // =====================
    // Relations
    // =====================
    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: Candidacy::class, orphanRemoval: true)]
    private Collection $candidacies;

    #[ORM\ManyToMany(targetEntity: JobOffer::class, mappedBy: 'candidates')]
    private Collection $jobOffers;

    // =====================
    // Constructor
    // =====================
    public function __construct()
    {
        $this->candidacies = new ArrayCollection();
        $this->jobOffers = new ArrayCollection();
    }

    // =====================
    // Getters & Setters
    // =====================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }
    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): static
    {
        $this->email = $email;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }
    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }
    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }
    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }
    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;
        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }
    public function setFacebook(?string $facebook): static
    {
        $this->facebook = $facebook;
        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }
    public function setNationalite(?string $nationalite): static
    {
        $this->nationalite = $nationalite;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }
    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }
    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getCvFile(): mixed
    {
        return $this->cvFile;
    }
    public function setCvFile(mixed $cvFile): static
    {
        $this->cvFile = $cvFile;
        return $this;
    }

    public function getLmFile(): mixed
    {
        return $this->lmFile;
    }
    public function setLmFile(mixed $lmFile): static
    {
        $this->lmFile = $lmFile;
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

    /**
     * @return Collection|Candidacy[]
     */
    public function getCandidacies(): Collection
    {
        return $this->candidacies;
    }

    public function getJobOffers(): Collection
    {
        return $this->jobOffers;
    }
}
