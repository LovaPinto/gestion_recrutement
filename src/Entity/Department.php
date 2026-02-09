<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $departmentName = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'departments', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    // ✅ MANAGER DU DÉPARTEMENT
    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $manager = null;

    // ---------------- GETTERS / SETTERS ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartmentName(): ?string
    {
        return $this->departmentName;
    }

    public function setDepartmentName(string $departmentName): static
    {
        $this->departmentName = $departmentName;
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

    public function getManager(): ?Users
    {
        return $this->manager;
    }

    public function setManager(Users $manager): static
    {
        $this->manager = $manager;
        return $this;
    }
}
