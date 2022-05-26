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
     * @ORM\Column(type="json")
     */
    private $parameter = [];

    /**
     * @ORM\Column(type="json")
     */
    private $images = [];

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
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

    public function __construct()
    {
        $this->additionalInfos = new ArrayCollection();
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

    public function getParameter(): ?array
    {
        return $this->parameter;
    }

    public function setParameter(array $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
}
