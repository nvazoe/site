<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuOptionProductsRepository")
 */
class MenuOptionProducts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MenuOption", inversedBy="yes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $menuOption;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="menuOptionProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $attribut;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenuOption(): ?MenuOption
    {
        return $this->menuOption;
    }

    public function setMenuOption(?MenuOption $menuOption): self
    {
        $this->menuOption = $menuOption;

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
    
    public function __toString(){
        return $this->product->getName();
    }

    public function getAttribut(): ?string
    {
        return $this->attribut;
    }

    public function setAttribut(string $attribut): self
    {
        $this->attribut = $attribut;

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
