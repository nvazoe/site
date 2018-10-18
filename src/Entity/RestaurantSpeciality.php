<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RestaurantSpecialityRepository")
 */
class RestaurantSpeciality
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="restaurantSpecialities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryMenu", inversedBy="restaurantSpecialities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategory(): ?CategoryMenu
    {
        return $this->category;
    }

    public function setCategory(?CategoryMenu $category): self
    {
        $this->category = $category;

        return $this;
    }
    
    public function __toString(){
        return $this->getCategory()->getName();
    }
}
