<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BLOB)]
    private mixed $cvFile = null;

    #[ORM\Column(type: Types::BLOB)]
    private mixed $lmFile = null;

    // ===== Relation vers Users =====
    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    // ===== Getters / Setters =====
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;
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
}
