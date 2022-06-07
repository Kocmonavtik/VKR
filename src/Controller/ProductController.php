<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\PropertyProduct;
use App\Form\CommentType;
use App\Form\ProductType;
use App\Form\ResponseCommentType;
use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ManufacturerRepository;
use App\Repository\ProductRepository;
use App\Repository\PropertyProductRepository;
use App\Repository\PropertyRepository;
use App\Service\ServiceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/", name="app_product_index", methods={"GET"})
     */
    public function index(Request $request): Response
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

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $items,
            'images' => $images,
            'properties' => $properties,
            'ratingProducts' => $ratingProducts,
            'productMinValue' => $minPrices,
        ]);
    }

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
     * @Route("/{id}", name="app_product_show", methods={"GET", "POST"})
     */
    public function show(Product $product, Request $request): Response
    {
        $offers[$product->getId()] = $this->additionalInfoRepository
            ->findBy(
                ['product' => $product],
                ['price' => 'ASC']
            );
        $itemComment = new Comment();
        $form = $this->createForm(CommentType::class, $itemComment);
        if ($this->getUser()) {
            $this->requestShow = $request;
            $itemComment = new Comment();
            $itemComment->setCustomer($this->getUser());
            //$itemComment->setDate(new \DateTime('now'));
            $form = $this->createForm(CommentType::class, $itemComment, array('product' => $product));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $itemComment->setDate(new \DateTime('now'));
                $this->commentRepository->add($itemComment, true);
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        $items = $this->searchFunctions->getCategories();
        $offers[$product->getId()] = $this->additionalInfoRepository
            ->findBy(
                ['product' => $product],
                ['price' => 'ASC']
            );
        $count = count($offers[$product->getId()]);

        if ($count % 2 === 0) {
            $medianOffer = array_slice($offers[$product->getId()], ($count - 2) / 2, 2);
            $medianPrice = ($medianOffer[0]->getPrice() + $medianOffer[1]->getPrice()) / 2;
        } else {
            $medianOffer = array_slice($offers[$product->getId()], ($count - 1) / 2, 1);
            $medianPrice = $medianOffer[0]->getPrice();
        }

        $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);


        $originalComments = [];
        $countComments = 0;
        $avgRating = 0;
        foreach ($offers[$product->getId()] as $offer) {
            $comments = $offer->getComments();
            $avgRating += $offer->getAverageRating();
            foreach ($comments as $comment) {
                if (empty($comment->getResponse())) {
                    $originalComments[] = $comment;
                    ++$countComments;
                }
            }
        }
        $avgRating = $avgRating / count($offers[$product->getId()]);



        return $this->render('product/show.html.twig', [
            'product' => $product,
            'categories' => $items,
            'offers' => $offers,
            'median' => $medianPrice,
            'properties' => $properties,
            'comments' => $originalComments,
            'countComments' => $countComments,
            'form' => $form->createView(),
            'request' => $request,
            'avgRating' => $avgRating
        ]);
    }

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
        $products = $this->productRepository->search($query);
        $items = $this->searchFunctions->getCategories();
        $pagination = $this->paginator->paginate($products, $pageRequest, 5);
        $images = $this->searchFunctions->getImages($products, 3);

        $properties = [];
        foreach ($products as $product) {
            $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);
        }
        $avgRatings = $this->serviceRepository->getAverageRatingAndMinPrice();
        $ratingProducts = [];
        $minPrices = [];
        foreach ($avgRatings as $avgRating) {
            $ratingProducts[$avgRating['product_id']] = $avgRating['avg'];
            $minPrices[$avgRating['product_id']] = $avgRating['min'];
        }


        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $items,
            'pagination' => $pagination,
            'images' => $images,
            'properties' => $properties,
            'ratingProducts' => $ratingProducts,
            'productMinValue' => $minPrices
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
            $products = [];
            $arrayObject = [];
            $manufacturers = [];
            $properties = [];
            $distinctProperties = [];
            foreach ($categories as $item) {
                $products[] = $item->getProducts();
            }
            foreach ($products as $product) {
                foreach ($product as $item) {
                    array_push($arrayObject, $item);
                    $nameManufacturer = $item->getManufacturer()->getName();
                    if (empty($manufacturers[$nameManufacturer])) {
                        $manufacturers[$nameManufacturer] = $nameManufacturer;
                    }
                    $properties[$item->getId()] = $item->getPropertyProducts();
                    foreach ($properties[$item->getId()] as $property) {
                        $value = $property->getValue();
                        $name = $property->getProperty()->getName();
                        if (empty($distinctProperties[$name][$value])) {
                            $distinctProperties[$name][$value] = $value;
                        }
                    }
                }
            }

            $images = $this->searchFunctions->getImages($arrayObject, 3);

            $pagination = $this->paginator->paginate($arrayObject, $pageRequest, 5);
            return $this->render('product/showCategoryProduct.html.twig', [
                'categories' => $items,
                'pagination' => $pagination,
                'images' => $images,
                'properties' => $properties,
                'manufacturers' => $manufacturers,
                'distinctProperties' => $distinctProperties,
                'parentCategories' => $parentCategories,
                'ratingProducts' => $ratingProducts,
                'minProductPrice' => $minPrices


            ]);
        }
        $products = $category->getProducts();

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
        return $this->render('product/showCategoryProduct.html.twig', [
            'categories' => $items,
            'pagination' => $pagination,
            'images' => $images,
            'properties' => $properties,
            'manufacturers' => $manufacturers,
            'distinctProperties' => $distinctProperties,
            'parentCategories' => $parentCategories,
            'ratingProducts' => $ratingProducts,
            'minProductPrice' => $minPrices,
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
            //return  $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('comment/_form.html.twig', [
            'form' => $form,
            'comment' => $itemComment,
        ]);
    }
}
