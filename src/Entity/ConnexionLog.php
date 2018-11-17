<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConnexionLogRepository")
 */
class ConnexionLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="connexionLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $role;

    /**
     * @ORM\Column(type="boolean")
     */
    private $connectStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="bigint")
     */
    private $startDatetime;

    /**
     * @ORM\Column(type="bigint")
     */
    private $endDatetime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getConnectStatus(): ?bool
    {
        return $this->connectStatus;
    }

    public function setConnectStatus(bool $connectStatus): self
    {
        $this->connectStatus = $connectStatus;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getStartDatetime(): ?int
    {
        return $this->startDatetime;
    }

    public function setStartDatetime(int $startDatetime): self
    {
        $this->startDatetime = $startDatetime;

        return $this;
    }

    public function getEndDatetime(): ?int
    {
        return $this->endDatetime;
    }

    public function setEndDatetime(int $endDatetime): self
    {
        $this->endDatetime = $endDatetime;

        return $this;
    }
}
