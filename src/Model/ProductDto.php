<?php

namespace App\Model;

use App\Entity\Product;
use App\Entity\PropertyProduct;
use phpDocumentor\Reflection\Types\Integer;

class ProductDto
{
    private $id;

    private $name;

    private $properties = [];

    private $images = [];

   /* private $category;


    private $manufacturer;


    private $additionalInfos;


    private $propertyProducts;*/

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
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
    public function setProperties($propertyProduct): self
    {
        foreach ($propertyProduct as $item) {
            $this->properties[$item->getProperty()->getName()] = $item->getValue();
        }
        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
    public function setImages($additionalInfos): self
    {
        foreach ($additionalInfos as $additionalInfo) {
            $this->images[] = $additionalInfo->getImage();
        }
        return $this;
    }
    public function getImages(): array
    {
        return $this->images;
    }


    public function dtoFromProduct(Product $product): self
    {
        $userDto = new self();
        $userDto->setId($product->getId());
        $userDto->setName($product->getName());
        $userDto->setProperties($product->getPropertyProducts());
        $userDto->setImages($product->getAdditionalInfos());
        return $userDto;
    }
}
