<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\PropertyProduct;
use App\Entity\Rating;
use App\Entity\Statistic;
use App\Form\CommentType;
use App\Form\ProductType;
use App\Form\ResponseCommentType;
use App\Form\Type\FilterType;
use App\Form\Type\SortType;
use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ManufacturerRepository;
use App\Repository\ProductRepository;
use App\Repository\PropertyProductRepository;
use App\Repository\PropertyRepository;
use App\Repository\RatingRepository;
use App\Service\ServiceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\SearchFunctions;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    private $requestShow;
    private $ResponceShow;
    private $productRepository;
    private $categoryRepository;
    private $paginator;
    private ManagerRegistry $doctrine;
    //private $request;
    private $additionalInfoRepository;
    private $searchFunctions;
    private $propertyProductRepository;
  /*  private $manufacturerRepository;
    private $propertyRepository;*/
    private $commentRepository;
    private ServiceRepository $serviceRepository;
    private $ratingRepository;

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
        ServiceRepository $serviceRepository,
        RatingRepository $ratingRepository,
        ManagerRegistry $doctrine
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
        $this->ratingRepository=$ratingRepository;
        $this->doctrine = $doctrine;
    }
    /**
     * @Route("/", name="app_product_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $items = $this->searchFunctions->getCategories();

        return $this->render('product/index.html.twig', [
            'categories' => $items,
        ]);
    }


    /**
     * @Route("/{id}", name="app_product_show", methods={"GET", "POST"})
     */
    public function show(Product $product, Request $request): Response
    {
       $this->registerStatisticProduct($product);

        $offers[$product->getId()] = $this->additionalInfoRepository
            ->findBy(
                [
                    'product' => $product,
                    'status' => 'complete'
                ],
                ['price' => 'ASC'],
            );
        if ($this->getUser()) {
            $currentComment = $this->commentRepository->getOriginalCommentCurrentUser($product, $this->getUser());
            $currentRating = $this->ratingRepository->getRatingCurrentUser($product, $this->getUser());
        } else {
            $currentComment = null;
            $currentRating = null;
        }

        $avgRating = 0;
        foreach ($offers[$product->getId()] as $offer) {
            $avgRating += $offer->getAverageRating();
        }


        $items = $this->searchFunctions->getCategories();
        $count = count($offers[$product->getId()]);

        if ($count % 2 === 0) {
            $medianOffer = array_slice($offers[$product->getId()], ($count - 2) / 2, 2);
            $medianPrice = ($medianOffer[0]->getPrice() + $medianOffer[1]->getPrice()) / 2;
        } else {
            $medianOffer = array_slice($offers[$product->getId()], ($count - 1) / 2, 1);
            $medianPrice = $medianOffer[0]->getPrice();
        }

        $avgRating = $avgRating / count($offers[$product->getId()]);
        $shops = $this->serviceRepository->getStoresProduct($product);
        $comments = $this->serviceRepository->getComments($product->getId());
        //var_dump($comments);
        $originalComments = [];
        $responseComments = [];
        $count = 0;
        foreach ($comments as $comment) {
            if ($comment['id']== null) {
                continue;
            }
            if ($comment['response_id'] === null) {
                $originalComments[$comment['id']] = $comment;
                $count++;
            } else {
                $responseComments[$comment['response_id']][] = $comment;
                $count++;
            }
        }
        /* foreach ($propertyProduct as $item) {
           if (empty($this->properties[$item->getProperty()->getName()])) {
               $this->properties[$item->getProperty()->getName()] = (string) $item->getValue();
           } else {
               $this->properties[$item->getProperty()->getName()] .= ' ' . $item->getValue();
           }
       }*/
        //$properties[$product->getId()] = $product->getPropertyProducts();
        $propertyProduct = $product->getPropertyProducts();
        $masPropertiesProduct = [];
        foreach ($propertyProduct as $item) {
            if (empty($masPropertiesProduct[$item->getProperty()->getName()])) {
                $masPropertiesProduct[$item->getProperty()->getName()] = $item->getValue();
            } else {
                $masPropertiesProduct[$item->getProperty()->getName()] .= ' ' . $item->getValue();
            }
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'categories' => $items,
            'offers' => $offers,
            'median' => $medianPrice,
            'originalComments' => $originalComments,
            'responseComments' => $responseComments,
            'avgRating' => $avgRating,
            'shops' => $shops,
            'currentComment' => $currentComment,
            'currentRating' => $currentRating,
            'count' => $count,
            'properties' => $masPropertiesProduct
        ]);
    }

    /**
     * @Route("/get/search", name="search_product", methods={"GET"})
     */
    public function searchProduct(Request $request): Response
    {
        $pageRequest = $request->query->getInt('page', 1);
        if ($pageRequest <= 0) {
            $pageRequest = 1;
        }

        $query = $request->query->get('q');
        //$products = $this->productRepository->search($query);
        $items = $this->searchFunctions->getCategories();
        //$pagination = $this->paginator->paginate($products, $pageRequest, 5);
        //$images = $this->searchFunctions->getImages($products, 3);

      /*  $properties = [];
        foreach ($products as $product) {
            $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);
        }*/
   /*     $avgRatings = $this->serviceRepository->getAverageRatingAndMinPrice();
        $ratingProducts = [];
        $minPrices = [];
        foreach ($avgRatings as $avgRating) {
            $ratingProducts[$avgRating['product_id']] = $avgRating['avg'];
            $minPrices[$avgRating['product_id']] = $avgRating['min'];
        }*/


        return $this->render('product/indexSearch.html.twig', [
           /* 'products' => $products,*/
            'categories' => $items,
            'search' => $query
        /*    'pagination' => $pagination,
            'images' => $images,
            'properties' => $properties,
            'ratingProducts' => $ratingProducts,
            'productMinValue' => $minPrices*/
        ]);
    }

    /**
     * @Route("/category/{id}", name="product_category", methods={"GET"})
     *
     */
    public function ProductCategory(
        Category $category,
        Request $request
    ) {
        $isFilters = false;
        $items = $this->searchFunctions->getCategories();

        $pageRequest = $request->query->getInt('page', 1);
        if ($pageRequest <= 0) {
            $pageRequest = 1;
        }

        $product = new Product();
        $avgRatings = $this->serviceRepository->getAverageRatingAndMinPrice();
        $ratingProducts = [];
        $minPrices = [];
        foreach ($avgRatings as $avgRating) {
            $ratingProducts[$avgRating['product_id']] = $avgRating['avg'];
            $minPrices[$avgRating['product_id']] = $avgRating['min'];
        }

        $parentCategories = $this->categoryRepository->findBy(['parent' => $category]);
        $categories = $this->categoryRepository->findBy(['parent' => $category->getId()]);


        if(!empty($request->query->get('filters'))){
            $productsFilter=$this->productRepository->getProductsWithFilter((array)$request->query->get('filters'), $category);
            //var_dump($productsFilter);
            $isFilters=true;
        }
        if ($categories) {
            $products = [];
            $arrayObject = [];
            foreach ($categories as $item) {
                $arrayObject[] = $item->getProducts();
            }
            foreach ($arrayObject as $product) {
                foreach ($product as $item) {
                    array_push($products, $item);
                }
            }
        } else{
            $products = $category->getProducts();
        }
        $manufacturers = [];
        $properties = [];
        $distinctProperties = [];

        foreach ($products as $product) {
            $properties[$product->getId()] = $product->getPropertyProducts();
            $nameManufacturer = $product->getManufacturer()->getName();
            if (empty($manufacturers[$nameManufacturer])) {
                $manufacturers[$nameManufacturer] = $nameManufacturer;
            }
            foreach ($properties[$product->getId()] as $property) {
                $value = $property->getValue();
                $name = $property->getProperty()->getName();
                if (empty($distinctProperties[$name][$value])) {
                    $distinctProperties[$name][$value] = $value;
                }
            }
        }

        $images = $this->searchFunctions->getImages($products, 3);
        if (!empty($request->query->get('filters'))) {
            $pagination = $this->paginator->paginate($productsFilter, $pageRequest, 5);
        } else {
            $pagination = $this->paginator->paginate($products, $pageRequest, 5);
        }

       /* $sortForm = $this->createForm(SortType::class, null, []);
        $sortForm->handleRequest($request);*/

        $form=$this->createForm(FilterType::class, null, [
            'manufacturer' => $manufacturers,
            'category' => $parentCategories,
            'properties' => $distinctProperties,
            'method' => 'GET'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'product_category', [
                'id' => $category->getId(),
                'filters' => $form->getData(),
            ],
                Response::HTTP_SEE_OTHER
            );
        }
        $masPropertiesProducts = [];
        foreach ($properties as $key=>$propertiesProduct) {
            foreach ($propertiesProduct as $property) {
                if(empty($masPropertiesProducts[$key][$property->getProperty()->getName()])) {
                    $masPropertiesProducts[$key][$property->getProperty()->getName()] = $property->getValue();
                } else {
                    $masPropertiesProducts[$key][$property->getProperty()->getName()] .= ' ' .$property->getValue();
                }
            }
        }
        return $this->render('product/showCategoryProduct.html.twig', [
            'categories' => $items,
            'pagination' => $pagination,
            'images' => $images,
            'properties' => $masPropertiesProducts,
            'manufacturers' => $manufacturers,
            'distinctProperties' => $distinctProperties,
            'parentCategories' => $parentCategories,
            'ratingProducts' => $ratingProducts,
            'minProductPrice' => $minPrices,
            'form' => $form->createView(),
            'Filters' => $isFilters,
            'category' => $category,
        ]);
    }

    public function commentFormAction(Product $product, Comment $comment): Response
    {
        $request = $this->requestShow;
        $itemComment = new Comment();
        $itemComment->setCustomer($this->getUser());
        $itemComment->setAdditionalInfo($comment->getAdditionalInfo());//$comment->getAdditionalInfo());
        //$itemComment->setDate(new \DateTime('now'));
        $itemComment->setResponse($comment);
        $form = $this->createForm(ResponseCommentType::class, $itemComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemComment->setDate(new \DateTime('now'));
            $this->commentRepository->add($itemComment, true);
            //return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
            return  $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('comment/_form.html.twig', [
            'form' => $form,
            'comment' => $itemComment,
        ]);
    }




    private function registerStatisticProduct(Product $product)
    {
        $statistic = new Statistic();
        $statistic->setProduct($product)
            ->setDateVisit(new \DateTime('now'));
        $manager = $this->doctrine->getManager();
        $manager->persist($statistic);
        $manager->flush($statistic);
    }



    /**
     * @Route("/category/{id}/dev", name="product_category_dev", methods={"GET"})
     *
     */
   /* public function ProductCategoryDev(
        Category $category,
        Request $request
    ) {
        $items = $this->searchFunctions->getCategories();

        $pageRequest = $request->query->getInt('page', 1);
        if ($pageRequest <= 0) {
            $pageRequest = 1;
        }


        $avgRatings = $this->serviceRepository->getAverageRatingAndMinPrice();
        $ratingProducts = [];
        $minPrices = [];
        foreach ($avgRatings as $avgRating) {
            $ratingProducts[$avgRating['product_id']] = $avgRating['avg'];
            $minPrices[$avgRating['product_id']] = $avgRating['min'];
        }

        $parentCategories = $this->categoryRepository->findBy(['parent' => $category]);
        $categories = $this->categoryRepository->findBy(['parent' => $category->getId()]);

        if ($categories) {
            $products=[];
            $arrayObject = [];
            foreach ($categories as $item) {
                $arrayObject[] = $item->getProducts();
            }
            foreach ($arrayObject as $product) {
                foreach ($product as $item) {
                    array_push($products, $item);
                }
            }
        }else{
            $products = $category->getProducts();
        }
        $manufacturers = [];
        $properties = [];
        $distinctProperties = [];

        foreach ($products as $product) {
            $properties[$product->getId()] = $product->getPropertyProducts();
            $nameManufacturer = $product->getManufacturer()->getName();
            if (empty($manufacturers[$nameManufacturer])) {
                $manufacturers[$nameManufacturer] = $nameManufacturer;
            }
            foreach ($properties[$product->getId()] as $property) {
                $value = $property->getValue();
                $name = $property->getProperty()->getName();
                if (empty($distinctProperties[$name][$value])) {
                    $distinctProperties[$name][$value] = $value;
                }
            }
        }

        $images = $this->searchFunctions->getImages($products, 3);
        $pagination = $this->paginator->paginate($products, $pageRequest, 5);

        $form=$this->createForm(FilterType::class, null, [
            'manufacturer' => $manufacturers,
            'category' => $parentCategories,
            'properties' => $distinctProperties,
            'method' => 'GET'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'product_category', [
                    'id' => $category->getId(),
                    'filters' => $form->getData()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('product/showCategoryProductDev.html.twig', [
            'categories' => $items,
            'pagination' => $pagination,
            'images' => $images,
            'properties' => $properties,
            'manufacturers' => $manufacturers,
            'distinctProperties' => $distinctProperties,
            'parentCategories' => $parentCategories,
            'ratingProducts' => $ratingProducts,
            'minProductPrice' => $minPrices,
            'form' => $form->createView(),
            'filters' => $request->query->get('filters')

        ]);
    }*/
    /**
     * @Route("/new", name="app_product_new", methods={"GET", "POST"})
     */
    /*public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product, ['product_id' => $product->getId()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->add($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }*/
    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     */
    /* public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
     {
         $form = $this->createForm(ProductType::class, $product);
         $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid()) {
             $productRepository->add($product, true);

             return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
         }

         return $this->renderForm('product/edit.html.twig', [
             'product' => $product,
             'form' => $form,
         ]);
     }*/

    /**
     * @Route("/{id}", name="app_product_delete", methods={"POST"})
     */
    /* public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
     {
         if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
             $productRepository->remove($product, true);
         }

         return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
     }*/
}
