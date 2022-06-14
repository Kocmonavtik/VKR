<?php

namespace App\Controller;

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

        $products = $this->productRepository->findAll();

        $properties = [];
        foreach ($products as $product) {
            $properties[$product->getId()] = $product->getPropertyProducts();
        }

        $avgRatings = $this->serviceRepository->getAverageRatingAndMinPrice();
        $ratingProducts = [];
        $minPrices = [];
        foreach ($avgRatings as $avgRating) {
            $ratingProducts[$avgRating['product_id']] = $avgRating['avg'];
            $minPrices[$avgRating['product_id']] = $avgRating['min'];
        }

        $images = $this->searchFunctions->getImages($products, 3);

        $pagination = $this->paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            5
        );

        $data=[];
        foreach ($products as $product){
            $data[]=[
                'id'=>$product->getId(),
                'name'=>$product->getName(),
            ];
        }
        return $this->json($data);
       /* return $this->json(
            [ 'pagination' => $pagination,
                'categories' => $items,
                'images' => $images,
                'properties' => $properties,
                'ratingProducts' => $ratingProducts,
                'productMinValue' => $minPrices,

            ]);*/

        /* return $this->render('product/index.html.twig', [
             'pagination' => $pagination,
             'categories' => $items,
             'images' => $images,
             'properties' => $properties,
             'ratingProducts' => $ratingProducts,
             'productMinValue' => $minPrices,
         ]);*/
    }



}
