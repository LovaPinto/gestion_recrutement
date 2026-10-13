<?php

namespace App\Entity;

use App\Repository\UsersRepository;
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

    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    // RÔLE (RH / MANAGER / CANDIDAT)
    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    // DÉPARTEMENT D’APPARTENANCE
    #[ORM\ManyToOne(targetEntity: Department::class)]
    private ?Department $department = null;

    // 🔗 RELATION INVERSE AVEC CANDIDATE (AJOUTÉ)
    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Candidate::class, cascade: ['persist', 'remove'])]
    private ?Candidate $candidate = null;

    // ---------------- GETTERS / SETTERS ----------------

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

    public function setRole(Role $role): static
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

    // 🔗 GETTER / SETTER CANDIDATE (AJOUTÉS)
    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): static
    {
        $this->candidate = $candidate;

        if ($candidate && $candidate->getUser() !== $this) {
            $candidate->setUser($this);
        }

        return $this;
    }

    // 🔍 HELPERS MÉTIER
    public function isManager(): bool
    {
        return $this->role?->getType() === 'Manager';
    }

    public function isRh(): bool
    {
        return $this->role?->getType() === 'RH';
    }
}
