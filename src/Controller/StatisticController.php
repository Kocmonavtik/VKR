<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ManufacturerRepository;
use App\Repository\StoreRepository;
use App\Service\SearchFunctions;
use App\Service\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatisticController extends AbstractController
{
    private $searchFunctions;
    private $categoryRepository;
    private $manufacturerRepository;
    private $storeRepository;
    private $serviceRepository;

    public function __construct(
        SearchFunctions $searchFunctions,
        CategoryRepository $categoryRepository,
        ManufacturerRepository $manufacturerRepository,
        StoreRepository $storeRepository,
        ServiceRepository $serviceRepository
    ) {
        $this->searchFunctions = $searchFunctions;
        $this->categoryRepository = $categoryRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->storeRepository = $storeRepository;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * @Route("/statistic", name="app_statistic")
     */
    public function index(): Response
    {
        $items = $this->searchFunctions->getCategories();
        return $this->render('statistic/index.html.twig', [
            'controller_name' => 'StatisticController',
            'categories' => $items
        ]);
    }
    /**
     * @Route("/statistic/filtersData", name="statistic_data",  methods={"GET", "POST"})
     */
    public function getData(): JsonResponse
    {
        //$categories = $this->categoryRepository->findAll();
        $categories = $this->serviceRepository->getCategories();
        $manufacturers = $this->serviceRepository->getManufacturers();
        $stores = $this->serviceRepository->getSores();
        return $this->json([
            'categories' => $categories,
            'manufacturers' => $manufacturers,
            'stores' => $stores,
        ]);
    }
    /**
     * @Route("/statistic/category", name="statistic_category",  methods={"GET", "POST"})
     */
    public function getCategoryData(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        if ($category === 'nothing') {
            $manufacturers = $this->serviceRepository->getManufacturers();
            $stores = $this->serviceRepository->getSores();
        } else {
            $manufacturers = $this->serviceRepository->getManufacturersCategory($category);
            $stores = $this->serviceRepository->getStoresCategory($category);
        }
        return $this->json([
            'manufacturers' => $manufacturers,
            'stores' => $stores
        ]);
    }
    /**
     * @Route("/statistic/getData", name="statistic_get_data",  methods={"GET", "POST"})
     */
    public function getStatisticData(Request $request): JsonResponse
    {
        $dataType = $request->query->get('dataType');
        $stores = $request->query->get('stores');
        $manufacturers = $request->query->get('manufacturers');
        $category = $request->query->get('category');
        //var_dump($stores, $manufacturers);
        $tmp = [];
        $ratingStore = [];
        if ($dataType === 'rating') {
            $result = $this->serviceRepository->getRatingBrandStore($stores, $manufacturers, $category);

            //var_dump($result);
            foreach ($result as $item) {
                $tmp[$item['nameStore']][$item['name']] = $item['avg'];
            }
            foreach ($tmp as $key => $items) {
                foreach ($manufacturers as $manufacturer) {
                    if (array_key_exists($manufacturer, $items)) {
                        $ratingStore[$key][] = (float) $items[$manufacturer];
                    } else {
                        $ratingStore[$key][] = (float) 0;
                    }
                }
                /*foreach ($items as $keyManifacturer=>$value){
                    if()
                }*/
            }
            //var_dump($tmp,$ratingStore);
            //var_dump($ratingStore);
            //var_dump($result);
        } else {
        }
        return $this->json([
            'result' => $ratingStore
        ]);
    }
}
