<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuRepository")
 * @Vich\Uploadable
 */
class Menu
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TypeMenu", inversedBy="menus")
     */
    private $typeMenu;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuMenuOption", mappedBy="menu", orphanRemoval=true)
     */
    private $menuMenuOptions;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryMenu", inversedBy="menus")
     */
    private $categoryMenu;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="menus")
     */
    private $restaurant;

    

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuNote", mappedBy="menu")
     */
    private $menuNotes;
    
    /**
     * @Vich\UploadableField(mapping="menus", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    

    public function __construct()
    {
        $this->menuMenuOptions = new ArrayCollection();
        $this->orderDetailsMenuProducts = new ArrayCollection();
        $this->menuNotes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTypeMenu(): ?TypeMenu
    {
        return $this->typeMenu;
    }

    public function setTypeMenu(?TypeMenu $typeMenu): self
    {
        $this->typeMenu = $typeMenu;

        return $this;
    }

    /**
     * @return Collection|MenuMenuOption[]
     */
    public function getMenuMenuOptions(): Collection
    {
        $criteria = Criteria::create()->orderBy(['position'=>'asc']);
        return $this->menuMenuOptions->matching($criteria);
    }
    
    

    public function addMenuMenuOption(MenuMenuOption $menuMenuOption): self
    {
        if (!$this->menuMenuOptions->contains($menuMenuOption)) {
            $this->menuMenuOptions[] = $menuMenuOption;
            $menuMenuOption->setMenu($this);
        }

        return $this;
    }

    public function removeMenuMenuOption(MenuMenuOption $menuMenuOption): self
    {
        if ($this->menuMenuOptions->contains($menuMenuOption)) {
            $this->menuMenuOptions->removeElement($menuMenuOption);
            // set the owning side to null (unless already changed)
            if ($menuMenuOption->getMenu() === $this) {
                $menuMenuOption->setMenu(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }
    
    public function __toString(){
        return $this->getName();
    }

    public function getCategoryMenu(): ?CategoryMenu
    {
        return $this->categoryMenu;
    }

    public function setCategoryMenu(?CategoryMenu $categoryMenu): self
    {
        $this->categoryMenu = $categoryMenu;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|OrderDetailsMenuProduct[]
     */
    public function getOrderDetailsMenuProducts(): Collection
    {
        return $this->orderDetailsMenuProducts;
    }

    public function addOrderDetailsMenuProduct(OrderDetailsMenuProduct $orderDetailsMenuProduct): self
    {
        if (!$this->orderDetailsMenuProducts->contains($orderDetailsMenuProduct)) {
            $this->orderDetailsMenuProducts[] = $orderDetailsMenuProduct;
            $orderDetailsMenuProduct->setMenu($this);
        }

        return $this;
    }

    public function removeOrderDetailsMenuProduct(OrderDetailsMenuProduct $orderDetailsMenuProduct): self
    {
        if ($this->orderDetailsMenuProducts->contains($orderDetailsMenuProduct)) {
            $this->orderDetailsMenuProducts->removeElement($orderDetailsMenuProduct);
            // set the owning side to null (unless already changed)
            if ($orderDetailsMenuProduct->getMenu() === $this) {
                $orderDetailsMenuProduct->setMenu(null);
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
            $menuNote->setMenu($this);
        }

        return $this;
    }

    public function removeMenuNote(MenuNote $menuNote): self
    {
        if ($this->menuNotes->contains($menuNote)) {
            $this->menuNotes->removeElement($menuNote);
            // set the owning side to null (unless already changed)
            if ($menuNote->getMenu() === $this) {
                $menuNote->setMenu(null);
            }
        }

        return $this;
    }
    
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
//        if ($image) {
//            // if 'updatedAt' is not defined in your entity, use another property
//            $this->updatedAt = new \DateTime('now');
//        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function getDeleteStatus(): ?bool
    {
        return $this->deleteStatus;
    }

    public function setDeleteStatus(bool $deleteStatus): self
    {
        $this->deleteStatus = $deleteStatus;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
