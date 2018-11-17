<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RestaurantRepository")
 * @Vich\Uploadable
 */
class Restaurant
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
     * @ORM\OneToMany(targetEntity="App\Entity\Menu", mappedBy="restaurant")
     */
    private $menus;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $latidude;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="restaurants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RestaurantNote", mappedBy="restaurant")
     */
    private $restaurantNotes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="restaurant", orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RestaurantSpeciality", mappedBy="restaurant")
     */
    private $restaurantSpecialities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DeliveryProposition", mappedBy="restaurant")
     */
    private $deliveryPropositions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket", mappedBy="restaurant")
     */
    private $tickets;
    
    /**
     * @Vich\UploadableField(mapping="restaurants", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="restaurant")
     */
    private $products;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->restaurantNotes = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->restaurantSpecialities = new ArrayCollection();
        $this->deliveryPropositions = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->products = new ArrayCollection();
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
     * @return Collection|Menu[]
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
            $menu->setRestaurant($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->contains($menu)) {
            $this->menus->removeElement($menu);
            // set the owning side to null (unless already changed)
            if ($menu->getRestaurant() === $this) {
                $menu->setRestaurant(null);
            }
        }

        return $this;
    }
    
    
    public function __toString(){
        return $this->getName();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getLatidude(): ?string
    {
        return $this->latidude;
    }

    public function setLatidude(?string $latidude): self
    {
        $this->latidude = $latidude;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

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
            $restaurantNote->setRestaurant($this);
        }

        return $this;
    }

    public function removeRestaurantNote(RestaurantNote $restaurantNote): self
    {
        if ($this->restaurantNotes->contains($restaurantNote)) {
            $this->restaurantNotes->removeElement($restaurantNote);
            // set the owning side to null (unless already changed)
            if ($restaurantNote->getRestaurant() === $this) {
                $restaurantNote->setRestaurant(null);
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
            $order->setRestaurant($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getRestaurant() === $this) {
                $order->setRestaurant(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|RestaurantSpeciality[]
     */
    public function getRestaurantSpecialities(): Collection
    {
        return $this->restaurantSpecialities;
    }

    public function addRestaurantSpeciality(RestaurantSpeciality $restaurantSpeciality): self
    {
        if (!$this->restaurantSpecialities->contains($restaurantSpeciality)) {
            $this->restaurantSpecialities[] = $restaurantSpeciality;
            $restaurantSpeciality->setRestaurant($this);
        }

        return $this;
    }

    public function removeRestaurantSpeciality(RestaurantSpeciality $restaurantSpeciality): self
    {
        if ($this->restaurantSpecialities->contains($restaurantSpeciality)) {
            $this->restaurantSpecialities->removeElement($restaurantSpeciality);
            // set the owning side to null (unless already changed)
            if ($restaurantSpeciality->getRestaurant() === $this) {
                $restaurantSpeciality->setRestaurant(null);
            }
        }

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
            $deliveryProposition->setRestaurant($this);
        }

        return $this;
    }

    public function removeDeliveryProposition(DeliveryProposition $deliveryProposition): self
    {
        if ($this->deliveryPropositions->contains($deliveryProposition)) {
            $this->deliveryPropositions->removeElement($deliveryProposition);
            // set the owning side to null (unless already changed)
            if ($deliveryProposition->getRestaurant() === $this) {
                $deliveryProposition->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setRestaurant($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getRestaurant() === $this) {
                $ticket->setRestaurant(null);
            }
        }

        return $this;
    }
    
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setRestaurant($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getRestaurant() === $this) {
                $product->setRestaurant(null);
            }
        }

        return $this;
    }
}
