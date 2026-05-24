<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EntryPointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntryPointRepository::class)]
class EntryPoint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?string $number = null;

    #[ORM\Column]
    private ?int $divisionId = null;

    /**
     * @var Collection<int, EntryPointAvailability>
     */
    #[ORM\OneToMany(targetEntity: EntryPointAvailability::class, mappedBy: 'entryPoint', orphanRemoval: true)]
    private Collection $entryPointAvailabilities;

    public function __construct()
    {
        $this->entryPointAvailabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getDivisionId(): ?int
    {
        return $this->divisionId;
    }

    public function setDivisionId(int $divisionId): static
    {
        $this->divisionId = $divisionId;

        return $this;
    }

    /**
     * @return Collection<int, EntryPointAvailability>
     */
    public function getEntryPointAvailabilities(): Collection
    {
        return $this->entryPointAvailabilities;
    }

    public function addEntryPointAvailability(EntryPointAvailability $entryPointAvailability): static
    {
        if (!$this->entryPointAvailabilities->contains($entryPointAvailability)) {
            $this->entryPointAvailabilities->add($entryPointAvailability);
            $entryPointAvailability->setEntryPoint($this);
        }

        return $this;
    }

    public function removeEntryPointAvailability(EntryPointAvailability $entryPointAvailability): static
    {
        if ($this->entryPointAvailabilities->removeElement($entryPointAvailability)) {
            // set the owning side to null (unless already changed)
            if ($entryPointAvailability->getEntryPoint() === $this) {
                $entryPointAvailability->setEntryPoint(null);
            }
        }

        return $this;
    }
}
