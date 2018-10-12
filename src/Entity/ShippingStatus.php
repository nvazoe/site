<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShippingStatusRepository")
 */
class ShippingStatus
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderShipping", mappedBy="status")
     */
    private $orderShippings;

    public function __construct()
    {
        $this->orderShippings = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|OrderShipping[]
     */
    public function getOrderShippings(): Collection
    {
        return $this->orderShippings;
    }

    public function addOrderShipping(OrderShipping $orderShipping): self
    {
        if (!$this->orderShippings->contains($orderShipping)) {
            $this->orderShippings[] = $orderShipping;
            $orderShipping->setStatus($this);
        }

        return $this;
    }

    public function removeOrderShipping(OrderShipping $orderShipping): self
    {
        if ($this->orderShippings->contains($orderShipping)) {
            $this->orderShippings->removeElement($orderShipping);
            // set the owning side to null (unless already changed)
            if ($orderShipping->getStatus() === $this) {
                $orderShipping->setStatus(null);
            }
        }

        return $this;
    }

    
}
