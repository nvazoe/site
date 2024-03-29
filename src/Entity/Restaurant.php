<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\Criteria;

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

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cp;
    
    
    private $distance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripeAccountId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $externalAccount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityAdditionnalOwners;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityAddressCity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityAddressline1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityAddressPostalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityBusinessName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityBusinessTax1d;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityDobDay;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityDobMonth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityDobYear;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityLastNane;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityPersonalAddressCity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityPersonalAddressLine1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityPersonalAddressCodePostal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalEntityType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tosAcceptanceDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tosAcceptance1p;

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
        return $this->getName()?$this->getName(): '' ;
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

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(?string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    public function getStripeAccountId(): ?string
    {
        return $this->stripeAccountId;
    }

    public function setStripeAccountId(?string $stripeAccountId): self
    {
        $this->stripeAccountId = $stripeAccountId;

        return $this;
    }

    public function getExternalAccount(): ?string
    {
        return $this->externalAccount;
    }

    public function setExternalAccount(?string $externalAccount): self
    {
        $this->externalAccount = $externalAccount;

        return $this;
    }

    public function getLegalEntityAdditionnalOwners(): ?string
    {
        return $this->legalEntityAdditionnalOwners;
    }

    public function setLegalEntityAdditionnalOwners(?string $legalEntityAdditionnalOwners): self
    {
        $this->legalEntityAdditionnalOwners = $legalEntityAdditionnalOwners;

        return $this;
    }

    public function getLegalEntityAddressCity(): ?string
    {
        return $this->legalEntityAddressCity;
    }

    public function setLegalEntityAddressCity(?string $legalEntityAddressCity): self
    {
        $this->legalEntityAddressCity = $legalEntityAddressCity;

        return $this;
    }

    public function getLegalEntityAddressline1(): ?string
    {
        return $this->legalEntityAddressline1;
    }

    public function setLegalEntityAddressline1(?string $legalEntityAddressline1): self
    {
        $this->legalEntityAddressline1 = $legalEntityAddressline1;

        return $this;
    }

    public function getLegalEntityAddressPostalCode(): ?string
    {
        return $this->legalEntityAddressPostalCode;
    }

    public function setLegalEntityAddressPostalCode(?string $legalEntityAddressPostalCode): self
    {
        $this->legalEntityAddressPostalCode = $legalEntityAddressPostalCode;

        return $this;
    }

    public function getLegalEntityBusinessName(): ?string
    {
        return $this->legalEntityBusinessName;
    }

    public function setLegalEntityBusinessName(?string $legalEntityBusinessName): self
    {
        $this->legalEntityBusinessName = $legalEntityBusinessName;

        return $this;
    }

    public function getLegalEntityBusinessTax1d(): ?string
    {
        return $this->legalEntityBusinessTax1d;
    }

    public function setLegalEntityBusinessTax1d(?string $legalEntityBusinessTax1d): self
    {
        $this->legalEntityBusinessTax1d = $legalEntityBusinessTax1d;

        return $this;
    }

    public function getLegalEntityDobDay(): ?string
    {
        return $this->legalEntityDobDay;
    }

    public function setLegalEntityDobDay(?string $legalEntityDobDay): self
    {
        $this->legalEntityDobDay = $legalEntityDobDay;

        return $this;
    }

    public function getLegalEntityDobMonth(): ?string
    {
        return $this->legalEntityDobMonth;
    }

    public function setLegalEntityDobMonth(?string $legalEntityDobMonth): self
    {
        $this->legalEntityDobMonth = $legalEntityDobMonth;

        return $this;
    }

    public function getLegalEntityDobYear(): ?string
    {
        return $this->legalEntityDobYear;
    }

    public function setLegalEntityDobYear(?string $legalEntityDobYear): self
    {
        $this->legalEntityDobYear = $legalEntityDobYear;

        return $this;
    }

    public function getLegalEntityFirstName(): ?string
    {
        return $this->legalEntityFirstName;
    }

    public function setLegalEntityFirstName(?string $legalEntityFirstName): self
    {
        $this->legalEntityFirstName = $legalEntityFirstName;

        return $this;
    }

    public function getLegalEntityLastNane(): ?string
    {
        return $this->legalEntityLastNane;
    }

    public function setLegalEntityLastNane(?string $legalEntityLastNane): self
    {
        $this->legalEntityLastNane = $legalEntityLastNane;

        return $this;
    }

    public function getLegalEntityPersonalAddressCity(): ?string
    {
        return $this->legalEntityPersonalAddressCity;
    }

    public function setLegalEntityPersonalAddressCity(?string $legalEntityPersonalAddressCity): self
    {
        $this->legalEntityPersonalAddressCity = $legalEntityPersonalAddressCity;

        return $this;
    }

    public function getLegalEntityPersonalAddressLine1(): ?string
    {
        return $this->legalEntityPersonalAddressLine1;
    }

    public function setLegalEntityPersonalAddressLine1(?string $legalEntityPersonalAddressLine1): self
    {
        $this->legalEntityPersonalAddressLine1 = $legalEntityPersonalAddressLine1;

        return $this;
    }

    public function getLegalEntityPersonalAddressCodePostal(): ?string
    {
        return $this->legalEntityPersonalAddressCodePostal;
    }

    public function setLegalEntityPersonalAddressCodePostal(?string $legalEntityPersonalAddressCodePostal): self
    {
        $this->legalEntityPersonalAddressCodePostal = $legalEntityPersonalAddressCodePostal;

        return $this;
    }

    public function getLegalEntityType(): ?string
    {
        return $this->legalEntityType;
    }

    public function setLegalEntityType(?string $legalEntityType): self
    {
        $this->legalEntityType = $legalEntityType;

        return $this;
    }

    public function getTosAcceptanceDate(): ?string
    {
        return $this->tosAcceptanceDate;
    }

    public function setTosAcceptanceDate(?string $tosAcceptanceDate): self
    {
        $this->tosAcceptanceDate = $tosAcceptanceDate;

        return $this;
    }

    public function getTosAcceptance1p(): ?string
    {
        return $this->tosAcceptance1p;
    }

    public function setTosAcceptance1p(?string $tosAcceptance1p): self
    {
        $this->tosAcceptance1p = $tosAcceptance1p;

        return $this;
    }
    
    public function getActiveProducts(): Collection
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('status', 1))
            ->orderBy(['id'=>'DESC']);
        
        return $this->products->matching($criteria);
    }
    
    public function getMenusOrderByPosition(): Collection
    {
        $criteria = Criteria::create()->orderBy(['position'=>'asc']);
        
        return $this->menus->matching($criteria);
    }
    
    public function getNote(){
        $som = 0;
        $orders = $this->getOrders();
        $total = 0;
        $stars = 0;
        foreach ($orders as $o){
            if($o->getShippingNote()){
                $total ++;
                $som += $o->getShippingNote()->getRestauNote();
            }
        }
        if($total)
            $stars = number_format(($som/$total)/2, 1);
        
        
        return array('stars' => $stars, 'avis' => $total);
    }
    
    
    public function getDistance(){
        return $this->distance;
    }
}
