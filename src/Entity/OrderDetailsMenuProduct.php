<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderDetailsMenuProductRepository")
 */
class OrderDetailsMenuProduct
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrderDetails", inversedBy="orderDetailsMenuProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $orderDetails;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Menu", inversedBy="orderDetailsMenuProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $menu;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="orderDetailsMenuProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderDetails(): ?OrderDetails
    {
        return $this->orderDetails;
    }

    public function setOrderDetails(?OrderDetails $orderDetails): self
    {
        $this->orderDetails = $orderDetails;

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
    
    public function __toString(){
        return $this->getProduct()->getName();
    }
}
