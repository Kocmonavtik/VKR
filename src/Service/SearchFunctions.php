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
            //$images[$product->getId()] = $this->additionalInfoRepository->findBy(['product' => $product], null, $limit);
            $images[$product->getId()] = $product->getAdditionalInfos();
        }
        return $images;
    }

    public function getCategories(): array
    {
        $items = [];
        $categories = $this->categoryRepository->findBy(['parent' => null]);
        $parentCategories = $this->categoryRepository->notNullParent();
        $parentCategoriesArray = [];
        foreach ($parentCategories as $parentCategory) {
            $parentCategoriesArray[$parentCategory->getParent()->getId()][] = $parentCategory;
        }
        foreach ($categories as $category) {
            if (empty($parentCategoriesArray[$category->getId()])) {
                $items[] = [$category, null];
            } else {
                $subCategories = $parentCategoriesArray[$category->getId()];
                //$subCategories = $this->categoryRepository->findBy(['parent' => $category->getId()]);
                $items[] = [$category, $subCategories];
            }
        }
        return $items;
    }
}
