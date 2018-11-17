<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentModeRepository")
 */
class PaymentMode
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="paymentMode")
     */
    private $command;

    public function __construct()
    {
        $this->command = new ArrayCollection();
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

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getCommand(): Collection
    {
        return $this->command;
    }

    public function addCommand(Order $command): self
    {
        if (!$this->command->contains($command)) {
            $this->command[] = $command;
            $command->setPaymentMode($this);
        }

        return $this;
    }

    public function removeCommand(Order $command): self
    {
        if ($this->command->contains($command)) {
            $this->command->removeElement($command);
            // set the owning side to null (unless already changed)
            if ($command->getPaymentMode() === $this) {
                $command->setPaymentMode(null);
            }
        }

        return $this;
    }
    
    public function __toString(){
        return $this->getName();
    }
}
