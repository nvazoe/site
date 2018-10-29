<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderShippingRepository")
 */
class OrderShipping
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="orderShippings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $command;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orderShippings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $deliveryUser;

    /**
     * @ORM\Column(type="float")
     */
    private $shippingCost;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ShippingStatus", inversedBy="orderShippings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDeliveryUser(): ?User
    {
        return $this->deliveryUser;
    }

    public function setDeliveryUser(?User $deliveryUser): self
    {
        $this->deliveryUser = $deliveryUser;

        return $this;
    }

    public function getShippingCost(): ?float
    {
        return $this->shippingCost;
    }

    public function setShippingCost(float $shippingCost): self
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    public function getStatus(): ?ShippingStatus
    {
        return $this->status;
    }

    public function setStatus(?ShippingStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
    
    public function __toString(){
        return $this->getStatus()->getName();
    }
}
