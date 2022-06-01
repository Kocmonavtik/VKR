<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
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
     * @ORM\ManyToMany(targetEntity=Category::class,inversedBy="products")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=Manufacturer::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manufacturer;

    /**
     * @ORM\OneToMany(targetEntity=AdditionalInfo::class, mappedBy="product")
     */
    private $additionalInfos;

    /**
     * @ORM\OneToMany(targetEntity=PropertyProduct::class, mappedBy="product", orphanRemoval=true)
     */
    private $propertyProducts;

    public function __construct()
    {
        $this->additionalInfos = new ArrayCollection();
        $this->category = new ArrayCollection();
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
     * @return Collection|Category[]
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category[] = $category;
        }
        return $this;
    }
    public function removeCategory(Category $category)
    {
        if ($this->category->contains($category)) {
            $this->category->removeElement($category);
        }
        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * @return Collection<int, AdditionalInfo>
     */
    public function getAdditionalInfos(): Collection
    {
        return $this->additionalInfos;
    }

    public function addAdditionalInfo(AdditionalInfo $additionalInfo): self
    {
        if (!$this->additionalInfos->contains($additionalInfo)) {
            $this->additionalInfos[] = $additionalInfo;
            $additionalInfo->setProduct($this);
        }

        return $this;
    }

    public function removeAdditionalInfo(AdditionalInfo $additionalInfo): self
    {
        if ($this->additionalInfos->removeElement($additionalInfo)) {
            // set the owning side to null (unless already changed)
            if ($additionalInfo->getProduct() === $this) {
                $additionalInfo->setProduct(null);
            }
        }

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
            $propertyProduct->setProduct($this);
        }

        return $this;
    }

    public function removePropertyProduct(PropertyProduct $propertyProduct): self
    {
        if ($this->propertyProducts->removeElement($propertyProduct)) {
            // set the owning side to null (unless already changed)
            if ($propertyProduct->getProduct() === $this) {
                $propertyProduct->setProduct(null);
            }
        }

        return $this;
    }
}
