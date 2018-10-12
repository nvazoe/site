<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
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
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuOptionProducts", mappedBy="product", orphanRemoval=true)
     */
    private $menuOptionProducts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderDetailsMenuProduct", mappedBy="product")
     */
    private $orderDetailsMenuProducts;

    

    public function __construct()
    {
        $this->menuOptionProducts = new ArrayCollection();
        $this->orderDetailsMenuProducts = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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
     * @return Collection|MenuOptionProducts[]
     */
    public function getMenuOptionProducts(): Collection
    {
        return $this->menuOptionProducts;
    }

    public function addMenuOptionProduct(MenuOptionProducts $menuOptionProduct): self
    {
        if (!$this->menuOptionProducts->contains($menuOptionProduct)) {
            $this->menuOptionProducts[] = $menuOptionProduct;
            $menuOptionProduct->setProduct($this);
        }

        return $this;
    }

    public function removeMenuOptionProduct(MenuOptionProducts $menuOptionProduct): self
    {
        if ($this->menuOptionProducts->contains($menuOptionProduct)) {
            $this->menuOptionProducts->removeElement($menuOptionProduct);
            // set the owning side to null (unless already changed)
            if ($menuOptionProduct->getProduct() === $this) {
                $menuOptionProduct->setProduct(null);
            }
        }

        return $this;
    }
    
    public function __toString(){
        return $this->getName();
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
            $orderDetailsMenuProduct->setProduct($this);
        }

        return $this;
    }

    public function removeOrderDetailsMenuProduct(OrderDetailsMenuProduct $orderDetailsMenuProduct): self
    {
        if ($this->orderDetailsMenuProducts->contains($orderDetailsMenuProduct)) {
            $this->orderDetailsMenuProducts->removeElement($orderDetailsMenuProduct);
            // set the owning side to null (unless already changed)
            if ($orderDetailsMenuProduct->getProduct() === $this) {
                $orderDetailsMenuProduct->setProduct(null);
            }
        }

        return $this;
    }

    
}
