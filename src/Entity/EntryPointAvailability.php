<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EntryPointAvailabilityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntryPointAvailabilityRepository::class)]
#[ApiResource]
class EntryPointAvailability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'entryPointAvailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EntryPoint $entryPoint = null;

    #[ORM\Column]
    private ?int $availableCount = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastScrapedAt = null;

    #[ORM\ManyToOne(inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MonitoredDate $monitoredDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntryPoint(): ?EntryPoint
    {
        return $this->entryPoint;
    }

    public function setEntryPoint(?EntryPoint $entryPoint): static
    {
        $this->entryPoint = $entryPoint;

        return $this;
    }

    public function getAvailableCount(): ?int
    {
        return $this->availableCount;
    }

    public function setAvailableCount(int $availableCount): static
    {
        $this->availableCount = $availableCount;

        return $this;
    }

    public function getLastScrapedAt(): ?\DateTimeImmutable
    {
        return $this->lastScrapedAt;
    }

    public function setLastScrapedAt(?\DateTimeImmutable $lastScrapedAt): static
    {
        $this->lastScrapedAt = $lastScrapedAt;

        return $this;
    }

    public function getMonitoredDate(): ?MonitoredDate
    {
        return $this->monitoredDate;
    }

    public function setMonitoredDate(?MonitoredDate $monitoredDate): static
    {
        $this->monitoredDate = $monitoredDate;

        return $this;
    }
}
