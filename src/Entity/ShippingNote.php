<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShippingNoteRepository")
 */
class ShippingNote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Order", inversedBy="shippingNote", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $command;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $restauNote;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $DeliverNote;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $perquisite;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getCommand(): ?Order
    {
        return $this->command;
    }

    public function setCommand(Order $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getRestauNote(): ?int
    {
        return $this->restauNote;
    }

    public function setRestauNote(?int $restauNote): self
    {
        $this->restauNote = $restauNote;

        return $this;
    }

    public function getDeliverNote(): ?int
    {
        return $this->DeliverNote;
    }

    public function setDeliverNote(?int $DeliverNote): self
    {
        $this->DeliverNote = $DeliverNote;

        return $this;
    }

    public function getPerquisite(): ?float
    {
        return $this->perquisite;
    }

    public function setPerquisite(?float $perquisite): self
    {
        $this->perquisite = $perquisite;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }
}
