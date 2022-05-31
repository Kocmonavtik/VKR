<?php

namespace App\Service;

use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Entity\Product;
use App\Form\ProductType;

class SearchFunctions
{
    private CategoryRepository $categoryRepository;
    private AdditionalInfoRepository $additionalInfoRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        AdditionalInfoRepository $additionalInfoRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->additionalInfoRepository = $additionalInfoRepository;
    }


    public function getImages($products, $limit): array
    {
        $images = [];
        foreach ($products as $product) {
            $images[$product->getId()] = $this->additionalInfoRepository->findBy(['product' => $product], null, $limit);
        }
        return $images;
    }

    public function getCategories(): array
    {
        $items = [];
        $categories = $this->categoryRepository->findBy(['parent' => null]);
        foreach ($categories as $category) {
            $subCategories = $this->categoryRepository->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }
        return $items;
    }
}
