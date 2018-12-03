<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BankCardRepository")
 */
class BankCard
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ownerName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cardNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securityCode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bankCards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $monthExp;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $yearExp;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Order", mappedBy="payment", cascade={"persist", "remove"})
     */
    private $command;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripeId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnerName(): ?string
    {
        return $this->ownerName;
    }

    public function setOwnerName(string $ownerName): self
    {
        $this->ownerName = $ownerName;

        return $this;
    }

    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(string $cardNumber): self
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function getSecurityCode(): ?string
    {
        return $this->securityCode;
    }

    public function setSecurityCode(string $securityCode): self
    {
        $this->securityCode = $securityCode;

        return $this;
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

    public function getMonthExp(): ?string
    {
        return $this->monthExp;
    }

    public function setMonthExp(string $monthExp): self
    {
        $this->monthExp = $monthExp;

        return $this;
    }

    public function getYearExp(): ?string
    {
        return $this->yearExp;
    }

    public function setYearExp(string $yearExp): self
    {
        $this->yearExp = $yearExp;

        return $this;
    }

    public function getCommand(): ?Order
    {
        return $this->command;
    }

    public function setCommand(?Order $command): self
    {
        $this->command = $command;

        // set (or unset) the owning side of the relation if necessary
        $newPayment = $command === null ? null : $this;
        if ($newPayment !== $command->getPayment()) {
            $command->setPayment($newPayment);
        }

        return $this;
    }
    
    public function __toString(){
        return $this->getOwnerName().'-'.$this->getId();
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }
}
