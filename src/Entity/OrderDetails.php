<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderDetailsRepository")
 */
class OrderDetails
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="orderDetails")
     * @ORM\JoinColumn(nullable=false)
     */
    private $command;

    

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $menuName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderDetailsMenuProduct", mappedBy="orderDetails")
     */
    private $orderDetailsMenuProducts;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    public function __construct()
    {
        $this->orderDetailsMenuProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommand(): ?Order
    {
        return $this->command;
    }

    public function setCommand(?Order $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

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

    public function getMenuName(): ?string
    {
        return $this->menuName;
    }

    public function setMenuName(string $menuName): self
    {
        $this->menuName = $menuName;

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
            $orderDetailsMenuProduct->setOrderDetails($this);
        }

        return $this;
    }

    public function removeOrderDetailsMenuProduct(OrderDetailsMenuProduct $orderDetailsMenuProduct): self
    {
        if ($this->orderDetailsMenuProducts->contains($orderDetailsMenuProduct)) {
            $this->orderDetailsMenuProducts->removeElement($orderDetailsMenuProduct);
            // set the owning side to null (unless already changed)
            if ($orderDetailsMenuProduct->getOrderDetails() === $this) {
                $orderDetailsMenuProduct->setOrderDetails(null);
            }
        }

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
    
    public function __toString(){
        return $this->getMenuName();
    }
}
