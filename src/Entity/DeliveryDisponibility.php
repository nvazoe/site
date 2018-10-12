<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryDisponibilityRepository")
 */
class DeliveryDisponibility
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $day;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $startHour;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $endHour;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="deliveryDisponibilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $deliveryMan;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(string $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getStartHour(): ?string
    {
        return $this->startHour;
    }

    public function setStartHour(string $startHour): self
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?string
    {
        return $this->endHour;
    }

    public function setEndHour(string $endHour): self
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getDeliveryMan(): ?User
    {
        return $this->deliveryMan;
    }

    public function setDeliveryMan(?User $deliveryMan): self
    {
        $this->deliveryMan = $deliveryMan;

        return $this;
    }
    
    public function __toString(){
        return $this->getDay();
    }
}
