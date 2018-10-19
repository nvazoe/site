<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryMenuRepository")
 * @Vich\Uploadable
 */
class CategoryMenu
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
     * @ORM\OneToMany(targetEntity="App\Entity\Menu", mappedBy="categoryMenu")
     */
    private $menus;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RestaurantSpeciality", mappedBy="category")
     */
    private $restaurantSpecialities;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;
    
    /**
     * @Vich\UploadableField(mapping="categories", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->restaurantSpecialities = new ArrayCollection();
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
            $menu->setCategoryMenu($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->contains($menu)) {
            $this->menus->removeElement($menu);
            // set the owning side to null (unless already changed)
            if ($menu->getCategoryMenu() === $this) {
                $menu->setCategoryMenu(null);
            }
        }

        return $this;
    }
    
    public function __toString(){
        return $this->getName();
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
            $restaurantSpeciality->setCategory($this);
        }

        return $this;
    }

    public function removeRestaurantSpeciality(RestaurantSpeciality $restaurantSpeciality): self
    {
        if ($this->restaurantSpecialities->contains($restaurantSpeciality)) {
            $this->restaurantSpecialities->removeElement($restaurantSpeciality);
            // set the owning side to null (unless already changed)
            if ($restaurantSpeciality->getCategory() === $this) {
                $restaurantSpeciality->setCategory(null);
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
}
