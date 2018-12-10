<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShippingLogRepository")
 */
class ShippingLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shippingLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $messenger;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Order", inversedBy="shippingLog", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $command;

    /**
     * @ORM\Column(type="bigint")
     */
    private $makeAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    public function getMessenger(): ?User
    {
        return $this->messenger;
    }

    public function setMessenger(?User $messenger): self
    {
        $this->messenger = $messenger;

        return $this;
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

    public function getMakeAt(): ?int
    {
        return $this->makeAt;
    }

    public function setMakeAt(int $makeAt): self
    {
        $this->makeAt = $makeAt;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
    
    
    public function __toString(){
        return $this->getMessenger()->getFirstname();
    }

}
