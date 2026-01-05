<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    /**
     * @var Collection<int, NewUser>
     */
    #[ORM\OneToMany(targetEntity: NewUser::class, mappedBy: 'role')]
    private Collection $newUsers;

    public function __construct()
    {
        $this->newUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, NewUser>
     */
    public function getNewUsers(): Collection
    {
        return $this->newUsers;
    }

    public function addNewUser(NewUser $newUser): static
    {
        if (!$this->newUsers->contains($newUser)) {
            $this->newUsers->add($newUser);
            $newUser->setRole($this);
        }

        return $this;
    }

    public function removeNewUser(NewUser $newUser): static
    {
        if ($this->newUsers->removeElement($newUser)) {
            // set the owning side to null (unless already changed)
            if ($newUser->getRole() === $this) {
                $newUser->setRole(null);
            }
        }

        return $this;
    }
}
