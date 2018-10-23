<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="sf_user")
 */
class User implements UserInterface, \Serializable
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
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="json")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Restaurant", mappedBy="owner")
     */
    private $restaurants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BankCard", mappedBy="user")
     */
    private $bankCards;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DeliveryDisponibility", mappedBy="deliveryMan")
     */
    private $deliveryDisponibilities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="client")
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RestaurantNote", mappedBy="user")
     */
    private $restaurantNotes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderShipping", mappedBy="deliveryUser")
     */
    private $orderShippings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuNote", mappedBy="user")
     */
    private $menuNotes;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     */
    private $state;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_updated;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
        $this->bankCards = new ArrayCollection();
        $this->deliveryDisponibilities = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->restaurantNotes = new ArrayCollection();
        $this->orderShippings = new ArrayCollection();
        $this->menuNotes = new ArrayCollection();
        $this->date_created = new \DateTime();
        $this->date_updated = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    
    public function eraseCredentials()
    {
    }
    
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized, array('allowed_classes' => false));
    }
    
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * @return Collection|Restaurant[]
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): self
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants[] = $restaurant;
            $restaurant->setOwner($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): self
    {
        if ($this->restaurants->contains($restaurant)) {
            $this->restaurants->removeElement($restaurant);
            // set the owning side to null (unless already changed)
            if ($restaurant->getOwner() === $this) {
                $restaurant->setOwner(null);
            }
        }

        return $this;
    }
    
    public function __toString(){
        return $this->getusername();
    }

    /**
     * @return Collection|BankCard[]
     */
    public function getBankCards(): Collection
    {
        return $this->bankCards;
    }

    public function addBankCard(BankCard $bankCard): self
    {
        if (!$this->bankCards->contains($bankCard)) {
            $this->bankCards[] = $bankCard;
            $bankCard->setUser($this);
        }

        return $this;
    }

    public function removeBankCard(BankCard $bankCard): self
    {
        if ($this->bankCards->contains($bankCard)) {
            $this->bankCards->removeElement($bankCard);
            // set the owning side to null (unless already changed)
            if ($bankCard->getUser() === $this) {
                $bankCard->setUser(null);
            }
        }

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return Collection|DeliveryDisponibility[]
     */
    public function getDeliveryDisponibilities(): Collection
    {
        return $this->deliveryDisponibilities;
    }

    public function addDeliveryDisponibility(DeliveryDisponibility $deliveryDisponibility): self
    {
        if (!$this->deliveryDisponibilities->contains($deliveryDisponibility)) {
            $this->deliveryDisponibilities[] = $deliveryDisponibility;
            $deliveryDisponibility->setDeliveryMan($this);
        }

        return $this;
    }

    public function removeDeliveryDisponibility(DeliveryDisponibility $deliveryDisponibility): self
    {
        if ($this->deliveryDisponibilities->contains($deliveryDisponibility)) {
            $this->deliveryDisponibilities->removeElement($deliveryDisponibility);
            // set the owning side to null (unless already changed)
            if ($deliveryDisponibility->getDeliveryMan() === $this) {
                $deliveryDisponibility->setDeliveryMan(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setClient($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getClient() === $this) {
                $order->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RestaurantNote[]
     */
    public function getRestaurantNotes(): Collection
    {
        return $this->restaurantNotes;
    }

    public function addRestaurantNote(RestaurantNote $restaurantNote): self
    {
        if (!$this->restaurantNotes->contains($restaurantNote)) {
            $this->restaurantNotes[] = $restaurantNote;
            $restaurantNote->setUser($this);
        }

        return $this;
    }

    public function removeRestaurantNote(RestaurantNote $restaurantNote): self
    {
        if ($this->restaurantNotes->contains($restaurantNote)) {
            $this->restaurantNotes->removeElement($restaurantNote);
            // set the owning side to null (unless already changed)
            if ($restaurantNote->getUser() === $this) {
                $restaurantNote->setUser(null);
            }
        }

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
            $orderShipping->setDeliveryUser($this);
        }

        return $this;
    }

    public function removeOrderShipping(OrderShipping $orderShipping): self
    {
        if ($this->orderShippings->contains($orderShipping)) {
            $this->orderShippings->removeElement($orderShipping);
            // set the owning side to null (unless already changed)
            if ($orderShipping->getDeliveryUser() === $this) {
                $orderShipping->setDeliveryUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MenuNote[]
     */
    public function getMenuNotes(): Collection
    {
        return $this->menuNotes;
    }

    public function addMenuNote(MenuNote $menuNote): self
    {
        if (!$this->menuNotes->contains($menuNote)) {
            $this->menuNotes[] = $menuNote;
            $menuNote->setUser($this);
        }

        return $this;
    }

    public function removeMenuNote(MenuNote $menuNote): self
    {
        if ($this->menuNotes->contains($menuNote)) {
            $this->menuNotes->removeElement($menuNote);
            // set the owning side to null (unless already changed)
            if ($menuNote->getUser() === $this) {
                $menuNote->setUser(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->date_updated;
    }

    public function setDateUpdated(\DateTimeInterface $date_updated): self
    {
        $this->date_updated = $date_updated;

        return $this;
    }
}
