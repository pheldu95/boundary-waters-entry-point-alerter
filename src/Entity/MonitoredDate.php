<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MonitoredDateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonitoredDateRepository::class)]
#[ApiResource]
class MonitoredDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(inversedBy: 'monitoredDates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, EntryPointAvailability>
     */
    #[ORM\OneToMany(targetEntity: EntryPointAvailability::class, mappedBy: 'monitoredDate', orphanRemoval: true)]
    private Collection $availabilities;

    public function __construct()
    {
        $this->availabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, EntryPointAvailability>
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(EntryPointAvailability $availability): static
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities->add($availability);
            $availability->setMonitoredDate($this);
        }

        return $this;
    }

    public function removeAvailability(EntryPointAvailability $availability): static
    {
        if ($this->availabilities->removeElement($availability)) {
            // set the owning side to null (unless already changed)
            if ($availability->getMonitoredDate() === $this) {
                $availability->setMonitoredDate(null);
            }
        }

        return $this;
    }
}
