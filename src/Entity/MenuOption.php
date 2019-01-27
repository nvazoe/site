<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuOptionRepository")
 */
class MenuOption
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
     * @ORM\OneToMany(targetEntity="App\Entity\MenuOptionProducts", mappedBy="menuOption", orphanRemoval=true)
     */
    private $yes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuMenuOption", mappedBy="menuOption", orphanRemoval=true)
     */
    private $menuMenuOptions;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $item;

    public function __construct()
    {
        $this->yes = new ArrayCollection();
        $this->menuMenuOptions = new ArrayCollection();
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
     * @return Collection|MenuOptionProducts[]
     */
    public function getYes(): Collection
    {
        $criteria = Criteria::create()->orderBy(['position'=>'asc']);
        return $this->yes->matching($criteria);;
    }

    public function addYe(MenuOptionProducts $ye): self
    {
        if (!$this->yes->contains($ye)) {
            $this->yes[] = $ye;
            $ye->setMenuOption($this);
        }

        return $this;
    }

    public function removeYe(MenuOptionProducts $ye): self
    {
        if ($this->yes->contains($ye)) {
            $this->yes->removeElement($ye);
            // set the owning side to null (unless already changed)
            if ($ye->getMenuOption() === $this) {
                $ye->setMenuOption(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MenuMenuOption[]
     */
    public function getMenuMenuOptions(): Collection
    {
        return $this->menuMenuOptions;
    }

    public function addMenuMenuOption(MenuMenuOption $menuMenuOption): self
    {
        if (!$this->menuMenuOptions->contains($menuMenuOption)) {
            $this->menuMenuOptions[] = $menuMenuOption;
            $menuMenuOption->setMenuOption($this);
        }

        return $this;
    }

    public function removeMenuMenuOption(MenuMenuOption $menuMenuOption): self
    {
        if ($this->menuMenuOptions->contains($menuMenuOption)) {
            $this->menuMenuOptions->removeElement($menuMenuOption);
            // set the owning side to null (unless already changed)
            if ($menuMenuOption->getMenuOption() === $this) {
                $menuMenuOption->setMenuOption(null);
            }
        }

        return $this;
    }

    
    public function __toString(){
        return $this->getName();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getItem(): ?int
    {
        return $this->item;
    }

    public function setItem(?int $item): self
    {
        $this->item = $item;

        return $this;
    }
    
}
