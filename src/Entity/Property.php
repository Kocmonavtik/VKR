<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PropertyRepository::class)
 */
class Property
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=PropertyProduct::class, mappedBy="property", orphanRemoval=true)
     */
    private $propertyProducts;

    public function __construct()
    {
        $this->propertyProducts = new ArrayCollection();
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
     * @return Collection<int, PropertyProduct>
     */
    public function getPropertyProducts(): Collection
    {
        return $this->propertyProducts;
    }

    public function addPropertyProduct(PropertyProduct $propertyProduct): self
    {
        if (!$this->propertyProducts->contains($propertyProduct)) {
            $this->propertyProducts[] = $propertyProduct;
            $propertyProduct->setProperty($this);
        }

        return $this;
    }

    public function removePropertyProduct(PropertyProduct $propertyProduct): self
    {
        if ($this->propertyProducts->removeElement($propertyProduct)) {
            // set the owning side to null (unless already changed)
            if ($propertyProduct->getProperty() === $this) {
                $propertyProduct->setProperty(null);
            }
        }

        return $this;
    }
}
