<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
}
