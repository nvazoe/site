<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="sf_order")
 */
class Order
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
    private $ref;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrderStatus", inversedBy="command")
     * @ORM\JoinColumn(nullable=false)
     */
    private $orderStatus;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderDetails", mappedBy="command")
     */
    private $orderDetails;

    

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $restaurant;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderShipping", mappedBy="command", orphanRemoval=true)
     */
    private $orderShippings;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trackingLng;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trackingLat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ordersDelivered")
     */
    private $messenger;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deliveryLocal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deliveryNote;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deliveryHour;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveryDate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $deliveryType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BankCard", inversedBy="command")
     */
    private $payment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DeliveryProposition", mappedBy="command")
     */
    private $deliveryPropositions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentMode", inversedBy="command")
     */
    private $paymentMode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ticket", inversedBy="commands")
     */
    private $ticket;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cp;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Facture", mappedBy="command", cascade={"persist", "remove"})
     */
    private $facture;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ShippingLog", mappedBy="command", cascade={"persist", "remove"})
     */
    private $shippingLog;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $balanceTransaction;

    /**
     * @ORM\Column(type="float")
     */
    private $commission;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ShippingNote", mappedBy="command", cascade={"persist", "remove"})
     */
    private $shippingNote;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $shippingCost;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->orderShippings = new ArrayCollection();
        $this->shippingStatuses = new ArrayCollection();
        $this->date_created = new \DateTime();
        $this->deliveryPropositions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getOrderStatus(): ?OrderStatus
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(?OrderStatus $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * @return Collection|OrderDetails[]
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails[] = $orderDetail;
            $orderDetail->setCommand($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->removeElement($orderDetail);
            // set the owning side to null (unless already changed)
            if ($orderDetail->getCommand() === $this) {
                $orderDetail->setCommand(null);
            }
        }

        return $this;
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
            $orderShipping->setCommand($this);
        }

        return $this;
    }

    public function removeOrderShipping(OrderShipping $orderShipping): self
    {
        if ($this->orderShippings->contains($orderShipping)) {
            $this->orderShippings->removeElement($orderShipping);
            // set the owning side to null (unless already changed)
            if ($orderShipping->getCommand() === $this) {
                $orderShipping->setCommand(null);
            }
        }

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated( $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
    
    public function __toString(){
        return $this->getRef();
    }

    public function getTrackingLng(): ?string
    {
        return $this->trackingLng;
    }

    public function setTrackingLng(?string $trackingLng): self
    {
        $this->trackingLng = $trackingLng;

        return $this;
    }

    public function getTrackingLat(): ?string
    {
        return $this->trackingLat;
    }

    public function setTrackingLat(?string $trackingLat): self
    {
        $this->trackingLat = $trackingLat;

        return $this;
    }

    public function getMessenger(): ?User
    {
        return $this->messenger;
    }

    public function setMessenger(?User $messenger): self
    {
        $this->messenger = $messenger;

        return $this;
    }

    public function getDeliveryLocal(): ?string
    {
        return $this->deliveryLocal;
    }

    public function setDeliveryLocal(?string $deliveryLocal): self
    {
        $this->deliveryLocal = $deliveryLocal;

        return $this;
    }

    public function getDeliveryNote(): ?string
    {
        return $this->deliveryNote;
    }

    public function setDeliveryNote(?string $deliveryNote): self
    {
        $this->deliveryNote = $deliveryNote;

        return $this;
    }

    public function getDeliveryHour(): ?string
    {
        return $this->deliveryHour;
    }

    public function setDeliveryHour(?string $deliveryHour): self
    {
        $this->deliveryHour = $deliveryHour;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeInterface $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getDeliveryType(): ?string
    {
        $text = "A DOMICILE";
        if($this->deliveryType == "HOME"){
            $text = "A DOMICILE";
        }elseif($this->deliveryType == "ON_ROAD"){
            $text = "AU VEHICULE";
        } else {
            $text;
        }
        return $text;
    }

    public function setDeliveryType(?string $deliveryType): self
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }

    public function getPayment(): ?BankCard
    {
        return $this->payment;
    }

    public function setPayment(?BankCard $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return Collection|DeliveryProposition[]
     */
    public function getDeliveryPropositions(): Collection
    {
        return $this->deliveryPropositions;
    }

    public function addDeliveryProposition(DeliveryProposition $deliveryProposition): self
    {
        if (!$this->deliveryPropositions->contains($deliveryProposition)) {
            $this->deliveryPropositions[] = $deliveryProposition;
            $deliveryProposition->setCommand($this);
        }

        return $this;
    }

    public function removeDeliveryProposition(DeliveryProposition $deliveryProposition): self
    {
        if ($this->deliveryPropositions->contains($deliveryProposition)) {
            $this->deliveryPropositions->removeElement($deliveryProposition);
            // set the owning side to null (unless already changed)
            if ($deliveryProposition->getCommand() === $this) {
                $deliveryProposition->setCommand(null);
            }
        }

        return $this;
    }

    public function getPaymentMode(): ?PaymentMode
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(?PaymentMode $paymentMode): self
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(?string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(Facture $facture): self
    {
        $this->facture = $facture;

        // set the owning side of the relation if necessary
        if ($this !== $facture->getCommand()) {
            $facture->setCommand($this);
        }

        return $this;
    }

    public function getShippingLog(): ?ShippingLog
    {
        return $this->shippingLog;
    }

    public function setShippingLog(ShippingLog $shippingLog): self
    {
        $this->shippingLog = $shippingLog;

        // set the owning side of the relation if necessary
        if ($this !== $shippingLog->getCommand()) {
            $shippingLog->setCommand($this);
        }

        return $this;
    }

    public function getBalanceTransaction(): ?string
    {
        return $this->balanceTransaction;
    }

    public function setBalanceTransaction(?string $balanceTransaction): self
    {
        $this->balanceTransaction = $balanceTransaction;

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setCommission(float $commission): self
    {
        $this->commission = $commission;

        return $this;
    }
    
    
    public function getAllozoeCommission(){
        return $this->getAmount()*$this->getCommission();
    }
    
    public function getRestauEarn(){
        return $this->getAmount() - $this->getAllozoeCommission();
    }

    public function getShippingNote(): ?ShippingNote
    {
        return $this->shippingNote;
    }

    public function setShippingNote(ShippingNote $shippingNote): self
    {
        $this->shippingNote = $shippingNote;

        // set the owning side of the relation if necessary
        if ($this !== $shippingNote->getCommand()) {
            $shippingNote->setCommand($this);
        }

        return $this;
    }
    
    public function getRestauNote(){
        $note = 0.0;
        if($this->getShippingNote()){
            $note = number_format(($this->getShippingNote()->getRestauNote()/2), 1);
        }
        return $note;
    }
    
    public function getDeliverNote(){
        $note = 0.0;
        if($this->getShippingNote()){
            $note = number_format(($this->getShippingNote()->getDeliverNote()/2), 1);
        }
        return $note;
    }
    
    public function getModePaiement(){
        $string = "";
        if($this->getPaymentMode()){
            switch ($this->getPaymentMode()->getId()){
                case 1:
                    $string = "Carte bancaire";
                    break;
                case 2:
                    $string = "Ticket";
                    break;
                default:
                    $sting = "Aucun mode de paiement sélectionné.";
                    break;
            }
        }
        
        
        return $string;
    }

    public function getShippingCost(): ?float
    {
        return $this->shippingCost;
    }

    public function setShippingCost(?float $shippingCost): self
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }
}
