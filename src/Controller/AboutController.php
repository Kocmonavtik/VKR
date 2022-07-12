<?php

namespace App\Controller;

use App\Model\ProductDto;
use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ProductRepository;
use App\Repository\PropertyProductRepository;
use App\Service\SearchFunctions;
use App\Service\ServiceRepository;
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
    private $requestShow;
    private $ResponceShow;
    private $productRepository;
    private $categoryRepository;
    private $paginator;
    //private $request;
    private $additionalInfoRepository;
    private $searchFunctions;
    private $propertyProductRepository;
    /*  private $manufacturerRepository;
      private $propertyRepository;*/
    private $commentRepository;
    private $serviceRepository;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        AdditionalInfoRepository $additionalInfoRepository,
        SearchFunctions $searchFunctions,
        PropertyProductRepository $propertyProductRepository,
        /* ManufacturerRepository $manufacturerRepository,
         PropertyRepository $propertyRepository,*/
        CommentRepository $commentRepository,
        ServiceRepository $serviceRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->paginator = $paginator;
        $this->additionalInfoRepository = $additionalInfoRepository;
        $this->searchFunctions = $searchFunctions;
        $this->propertyProductRepository = $propertyProductRepository;
        /* $this->manufacturerRepository = $manufacturerRepository;
         $this->propertyRepository = $propertyRepository;*/
        $this->commentRepository = $commentRepository;
        $this->serviceRepository = $serviceRepository;
    }





    /**
     * @Route("/about", name="app_about")
     */
    public function index(): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_USER');
        return new RedirectResponse($this->generateUrl('app_product_index'));

       /* {% if is_granted('ROLE_ADMIN') %}
        <li class="nav-item">
                            <a class="nav-link" href="{{ path('admin_dashboard') }}">Admin</a>
                        </li>
                        {% endif %}
       is_granted('IS_AUTHENTICATED_FULLY'):// после авторизации текущей сессии
       IS_AUTHENTICATED_REMEMBERED после ремембер ми и заходе в браузер
       */
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
        //$items = $this->searchFunctions->getCategories();
        /*  switch ($filter){
              case 'popularity':
                  break;
              case 'rating':
                  break;
              case 'priceUp':
                  break;
              case 'priceDown':
                  break;
          }*/
        if(!empty($request->query->get('page'))){
            $page = $request->query->get('page');
        }else{$page=1;}
        if(!empty($request->query->get('filter'))){
            $filter=$request->query->get('filter');
        }else{$filter='popularity';}
        switch ($filter){
            case 'rating':
                $products = $this->productRepository->sortProductRating();
                break;
            case 'priceUp':
                $products = $this->productRepository->sortProductMaxPrice();
                break;
            case 'priceDown':
                $products = $this->productRepository->sortProductMinPrice();
                break;
            default:
                $products = $this->productRepository->findAll();
        }

        $dtoProducts=[];
        foreach ($products as $product){
            $productDto= new ProductDto();
            $dtoProducts[]= $productDto->dtoFromProduct($product);
        }

        $avgRatings = $this->serviceRepository->getAverageRatingAndMinPrice();
        $ratingProducts = [];
        $minPrices = [];
        foreach ($avgRatings as $avgRating) {
            $ratingProducts[$avgRating['product_id']] = $avgRating['avg'];
            $minPrices[$avgRating['product_id']] = $avgRating['min'];
        }


        $pagination= $this->paginator->paginate(
            $dtoProducts,
            $page,
            5
        );

        return $this->json([
            'pagination' => $pagination,
            'ratingProducts' => $ratingProducts,
            'productMinValue' => $minPrices,
            'filter' => $filter,
            'page' => $page,
        ]);
    }

}
