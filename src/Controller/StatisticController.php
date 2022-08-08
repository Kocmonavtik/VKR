<?php

namespace App\Controller;

use App\Entity\AdditionalInfo;
use App\Entity\Product;
use App\Entity\Statistic;
use App\Form\DateType;
use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\ManufacturerRepository;
use App\Repository\StoreRepository;
use App\Service\SearchFunctions;
use App\Service\ServiceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatisticController extends AbstractController
{
    private SearchFunctions $searchFunctions;
/*    private $categoryRepository;
    private $manufacturerRepository;
    private $storeRepository;*/
    private ServiceRepository $serviceRepository;
    private AdditionalInfoRepository $additionalInfoRepository;
    private ManagerRegistry $doctrine;

    public function __construct(
        SearchFunctions $searchFunctions,
      /*  CategoryRepository $categoryRepository,
        ManufacturerRepository $manufacturerRepository,
        StoreRepository $storeRepository,*/
        ServiceRepository $serviceRepository,
        AdditionalInfoRepository $additionalInfoRepository,
        ManagerRegistry $doctrine
    ) {
        $this->searchFunctions = $searchFunctions;
     /*   $this->categoryRepository = $categoryRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->storeRepository = $storeRepository;*/
        $this->serviceRepository = $serviceRepository;
        $this->additionalInfoRepository = $additionalInfoRepository;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/statistic", name="app_statistic")
     */
    public function index(): Response
    {
        $form = $this->createForm(DateType::class);
        $items = $this->searchFunctions->getCategories();
        return $this->render('statistic/index.html.twig', [
           /* 'controller_name' => 'StatisticController',*/
            'categories' => $items,
            'dateForm' => $form->createView()
        ]);
    }
    /**
     *@Route("/statistic/product/{id}", name="product_statistic", methods={"GET", "POST"})
     */
    public function indexProduct(Product $product): Response
    {
        $form = $this->createForm(DateType::class);
        $items = $this->searchFunctions->getCategories();
        $offers[$product->getId()] = $this->additionalInfoRepository
            ->findBy(
                ['product' => $product],
                ['price' => 'ASC']
            );
        $shops = $this->serviceRepository->getStoresProduct($product);

        $propertyProduct = $product->getPropertyProducts();
        $masPropertiesProduct = [];
        foreach ($propertyProduct as $item) {
            if (empty($masPropertiesProduct[$item->getProperty()->getName()])) {
                $masPropertiesProduct[$item->getProperty()->getName()] = $item->getValue();
            } else {
                $masPropertiesProduct[$item->getProperty()->getName()] .= ' ' . $item->getValue();
            }
        }
        return $this->render('statistic/indexProduct.html.twig', [
            'categories' => $items,
            'dateForm' => $form->createView(),
            'product' => $product,
            'offers' => $offers,
            'shops' => $shops,
            'properties' => $masPropertiesProduct

        ]);
    }
    /**
     * @Route ("/statistic/productData", name="get_product_statistic", methods={"GET","POST"})
     */
    public function loadProduct(Request $request): JsonResponse
    {
        $dataType = $request->query->get('dataType');
        $stores = $request->query->get('stores');
        $id = $request->query->get('productId');

        $dateFirst = $request->query->get('dateFirst');
        $dateSecond = $request->query->get('dateSecond');
        $statisticStore = [];
        if ($dataType === 'rating') {
            $result = $this->serviceRepository->getRatingProductStore(
                $stores,
                $dateFirst,
                $dateSecond,
                $id
            );

            foreach ($result as $item) {
                $statisticStore[$item['nameStore']] = $item['avg'];
            }
        } else {
            $result = $this->serviceRepository->getVisitProductStore(
                $stores,
                $dateFirst,
                $dateSecond,
                $id
            );

            foreach ($result as $item) {
                $statisticStore[$item['nameStore']] = (float) $item['count'];
            }

            $resultProduct = $this->serviceRepository->getVisitProduct(
                $dateFirst,
                $dateSecond,
                $id
            );
            foreach ($resultProduct as $item) {
                $statisticStore['Посещений на сайте'] = (float) $item['count'];
            }

        }
        return $this->json([
            'result' => $statisticStore
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
            'stores' => $stores,
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
        $statisticStore = [];
        $dateFirst = $request->query->get('dateFirst');
        $dateSecond = $request->query->get('dateSecond');
        if ($dataType === 'rating') {
            $result = $this->serviceRepository->getRatingBrandStore(
                $stores,
                $manufacturers,
                $category,
                $dateFirst,
                $dateSecond
            );
            foreach ($result as $item) {
                $tmp[$item['nameStore']][$item['name']] = $item['avg'];
            }
            foreach ($tmp as $key => $items) {
                foreach ($manufacturers as $manufacturer) {
                    if (array_key_exists($manufacturer, $items)) {
                        $statisticStore[$key][] = (float) $items[$manufacturer];
                    } else {
                        $statisticStore[$key][] = (float) 0;
                    }
                }
            }
        } else {
            $result = $this->serviceRepository->getVisitBrandStore(
                $stores,
                $manufacturers,
                $category,
                $dateFirst,
                $dateSecond
            );
           $resultProduct = $this->serviceRepository->getVisitBrandProduct(
                $manufacturers,
                $category,
                $dateFirst,
                $dateSecond
            );
            foreach ($result as $item) {
                $tmp[$item['nameStore']][$item['name']] = $item['count'];
            }
            foreach ($tmp as $key => $items) {
                foreach ($manufacturers as $manufacturer) {
                    if (array_key_exists($manufacturer, $items)) {
                        $statisticStore[$key][] = (float) $items[$manufacturer];
                    } else {
                        $statisticStore[$key][] = 0.0;
                    }
                }
            }

            $tmp = [];
            foreach ($resultProduct as $item) {
                $tmp[$item['name']] = $item['count'];
            }

            foreach ($manufacturers as $manufacturer) {
                if (array_key_exists($manufacturer, $tmp)) {
                    $statisticStore['Посещений на сайте'][] = (float) $tmp[$manufacturer];
                } else {
                    $statisticStore['Посещений на сайте'][] = 0.0;
                }
            }
        }
        return $this->json([
            'result' => $statisticStore
        ]);
    }
    /**
     *@Route("/statistic/offer/{id}", name="set_statistic_offer", methods={"GET", "POST"})
     */
    public function setStatistic(AdditionalInfo $offer)
    {
        $manager = $this->doctrine->getManager();
        $statistic = new Statistic();
        $statistic->setDateVisit(new \DateTime('now'))
            ->setProduct($offer->getProduct())
            ->setAdditionalInfo($offer);
        $manager->persist($statistic);
        $manager->flush();
        return $this->redirect($offer->getUrl(),308);
    }
}
