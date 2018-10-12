<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RestaurantNoteRepository")
 */
class RestaurantNote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="restaurantNotes")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="restaurantNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $restaurant;

    /**
     * @ORM\Column(type="integer")
     */
    private $note;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;

        return $this;
    }
    
    public function __toString(){
        return $this->getRestaurant()->getName();
    }
}
