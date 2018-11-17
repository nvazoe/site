<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryPropositionRepository")
 */
class DeliveryProposition
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="deliveryPropositions")
     */
    private $restaurant;

    /**
     * @ORM\Column(type="smallint")
     */
    private $valueResto;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="deliveryPropositions")
     */
    private $deliver;

    /**
     * @ORM\Column(type="smallint")
     */
    private $valueDeliver;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="deliveryPropositions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $command;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getValueResto(): ?int
    {
        return $this->valueResto;
    }

    public function setValueResto(int $valueResto): self
    {
        $this->valueResto = $valueResto;

        return $this;
    }

    public function getDeliver(): ?User
    {
        return $this->deliver;
    }

    public function setDeliver(?User $deliver): self
    {
        $this->deliver = $deliver;

        return $this;
    }

    public function getValueDeliver(): ?int
    {
        return $this->valueDeliver;
    }

    public function setValueDeliver(int $valueDeliver): self
    {
        $this->valueDeliver = $valueDeliver;

        return $this;
    }

    public function getCommand(): ?Order
    {
        return $this->command;
    }

    public function setCommand(?Order $command): self
    {
        $this->command = $command;

        return $this;
    }
    
    public function __toString(){
        return $this->getRestaurant()->getName().' - '.$this->getDeliver()->getLastname();
    }
}
