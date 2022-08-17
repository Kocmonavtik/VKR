<?php

namespace App\Controller;

use App\Entity\AdditionalInfo;
use App\Entity\Product;
use App\Entity\Statistic;
use App\Model\ProductDto;
use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ProductRepository;
use App\Repository\PropertyProductRepository;
use App\Service\SearchFunctions;
use App\Service\ServiceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    private ProductRepository $productRepository;
    //private $categoryRepository;
    private PaginatorInterface $paginator;
    //private $additionalInfoRepository;
    private SearchFunctions $searchFunctions;
    //private $propertyProductRepository;
    //private $commentRepository;
    private ServiceRepository $serviceRepository;
    private ManagerRegistry $doctrine;

    public function __construct(
        ProductRepository $productRepository,
        //CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        //AdditionalInfoRepository $additionalInfoRepository,
        SearchFunctions $searchFunctions,
        //PropertyProductRepository $propertyProductRepository,
        //ManufacturerRepository $manufacturerRepository,
        //PropertyRepository $propertyRepository,
        //CommentRepository $commentRepository,
        ServiceRepository $serviceRepository,
        ManagerRegistry $doctrine
    ) {
        $this->productRepository = $productRepository;
        //$this->categoryRepository = $categoryRepository;
        $this->paginator = $paginator;
        //$this->additionalInfoRepository = $additionalInfoRepository;
        $this->searchFunctions = $searchFunctions;
        //$this->propertyProductRepository = $propertyProductRepository;
        /* $this->manufacturerRepository = $manufacturerRepository;
         $this->propertyRepository = $propertyRepository;*/
        //$this->commentRepository = $commentRepository;
        $this->serviceRepository = $serviceRepository;
        $this->doctrine = $doctrine;
    }





   /* public function dev(): Response
    {
        $manager = $this->doctrine->getManager();
        $products = $manager->getRepository(Product::class)->findAll();
        $countStatistic = $this->serviceRepository->tmpCount();
        $countsStatistic = [];
        foreach ($countStatistic as $statistic) {
            $countsStatistic[$statistic['id']] = $statistic['count'];
        }
        //var_dump($countsStatistic);
        foreach ($products as $product) {
            $count = (int) $countsStatistic[$product->getId()];
            $rndStatistic = random_int($count, $count + 500);
            for ($i = 0; $i < $rndStatistic; ++$i) {
                $statistic = new Statistic();
                $statistic->setProduct($product)
                    ->setDateVisit(new \DateTime('now'));
                $manager->persist($statistic);
            }
            $manager->flush();
        }
        $manager->flush();
        return $this->render('about/index.html.twig', [
            'controller_name' => 'about controller'
        ]);
    }*/

   /**
     * @Route("/about", name="app_about")
     */
    public function index(): Response
    {
        return new RedirectResponse($this->generateUrl('app_product_index'));
    }

    /**
     * @Route("/about/dev", name="app_product_index_dev", methods={"GET"})
     */
    public function indexDev(Request $request): Response
    {
        $items = $this->searchFunctions->getCategories();

         return $this->render('product/indexDev.html.twig', [
             'categories' => $items,
         ]);
    }

    /**
     * @Route("/about/filters", name="product_filters", methods={"GET", "POST"})
     */
    public function getProductsWithFilter(Request $request): JsonResponse
    {
        $manager = $this->doctrine->getManager();
        $colElementsPerPage = 5;
        if (!empty($request->query->get('page'))) {
            $page = $request->query->get('page');
        } else {
            $page = 1;
        }
        if (!empty($request->query->get('filter'))) {
            $filter = $request->query->get('filter');
        } else {
            $filter = 'rating';
        }
        $search = null;
        if (!empty($request->query->get('search'))) {
            $search = $request->query->get('search');
        }
        switch ($filter) {
            case 'rating':
                $products = $this->productRepository->sortProductRating($search);
                break;
            case 'priceUp':
                $products = $this->productRepository->sortProductMaxPrice($search);
                break;
            case 'priceDown':
                $products = $this->productRepository->sortProductMinPrice($search);
                break;
            default:
                $products = $this->productRepository->findAll();
        }

        $dtoProducts = [];
        $medianPrice = [];
        $minPrices = [];
        $maxPrices = [];
        foreach ($products as $product) {
            $productDto = new ProductDto();
            $dtoProducts[] = $productDto->dtoFromProduct($product);
            $offers = $manager->getRepository(AdditionalInfo::class)
                ->findBy(
                    [
                        'product' => $product,
                        'status' => 'complete'
                    ],
                    ['price' => 'ASC'],
                );
            $id = $product->getId();
            $count = count($offers);
            if ($count % 2 === 0) {
                $medianOffer = array_slice($offers, ($count - 2) / 2, 2);
                $medianPrice[$id] = ($medianOffer[0]->getPrice() + $medianOffer[1]->getPrice()) / 2 ;
            } else {
                $medianOffer = array_slice($offers, ($count - 1) / 2, 1);
                $medianPrice[$id] = $medianOffer[0]->getPrice();
            }
           /* $minPrices[$id] = $offers[0]->getPrice();
            $maxPrices[$id] = end($offers)->getPrice();*/
        }

        $avgRatings = $this->serviceRepository->getAverageRatingAndMinPrice();
        $ratingProducts = [];
        //$minPrices = [];
        foreach ($avgRatings as $avgRating) {
            $ratingProducts[$avgRating['product_id']] = $avgRating['avg'];
            $minPrices[$avgRating['product_id']] = $avgRating['min'];
            $maxPrices[$avgRating['product_id']] = $avgRating['max'];
        }


        $pagination = $this->paginator->paginate(
            $dtoProducts,
            $page,
            $colElementsPerPage
        );
        $colPages = ceil(count($dtoProducts) / $colElementsPerPage);

        return $this->json([
            'pagination' => $pagination,
            'ratingProducts' => $ratingProducts,
            'productMinValue' => $minPrices,
            'productMaxValue' => $maxPrices,
            'medianPrice' => $medianPrice,
            'filter' => $filter,
            'page' => $page,
            'colPages' => $colPages
        ]);
    }
}
