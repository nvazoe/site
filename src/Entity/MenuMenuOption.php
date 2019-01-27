<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuMenuOptionRepository")
 */
class MenuMenuOption
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Menu", inversedBy="menuMenuOptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $menu;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MenuOption", inversedBy="menuMenuOptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $menuOption;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMenuOption(): ?MenuOption
    {
        return $this->menuOption;
    }

    public function setMenuOption(?MenuOption $menuOption): self
    {
        $this->menuOption = $menuOption;

        return $this;
    }
    
    public function __toString(){
        return $this->menuOption->getName();
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
