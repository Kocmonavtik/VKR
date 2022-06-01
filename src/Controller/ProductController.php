<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\PropertyProduct;
use App\Form\ProductType;
use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PropertyProductRepository;
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
    private $productRepository;
    private $categoryRepository;
    private $paginator;
    //private $request;
    private $additionalInfoRepository;
    private $searchFunctions;
    private $propertyProductRepository;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        AdditionalInfoRepository $additionalInfoRepository,
        SearchFunctions $searchFunctions,
        PropertyProductRepository $propertyProductRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->paginator = $paginator;
        $this->additionalInfoRepository = $additionalInfoRepository;
        $this->searchFunctions = $searchFunctions;
        $this->propertyProductRepository = $propertyProductRepository;
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
            $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);
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
            'properties' => $properties
        ]);
    }

    /**
     * @Route("/new", name="app_product_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->add($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        $items = $this->searchFunctions->getCategories();
        $offers[$product->getId()] = $this->additionalInfoRepository
            ->findBy(
                ['product' => $product],
                ['price' => 'ASC']
            );
        $count = count($offers[$product->getId()]);

        if ($count % 2 === 0) {
            $medianOffer = array_slice($offers[$product->getId()], ($count - 2) / 2, 2);
            $medianPrice = ($medianOffer[0]->getPrice() + $medianOffer[0]->getPrice()) / 2;
        } else {
            $medianOffer = array_slice($offers[$product->getId()], ($count - 1) / 2, 1);
            $medianPrice = $medianOffer[0]->getPrice();
        }

        $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);


        return $this->render('product/show.html.twig', [
            'product' => $product,
            'categories' => $items,
            'offers' => $offers,
            'median' => $medianPrice,
            'properties' => $properties
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
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
    }

    /**
     * @Route("/{id}", name="app_product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/get/search", name="search_product", methods={"GET"})
     */
    public function searchProduct(Request $request): Response
    {
        $query = $request->query->get('q');
        $products = $this->productRepository->search($query);
        $items = $this->searchFunctions->getCategories();
        $pagination = $this->paginator->paginate($products, 1, 5);
        $images = $this->searchFunctions->getImages($products, 3);

        $properties = [];
        foreach ($products as $product) {
            $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $items,
            'pagination' => $pagination,
            'images' => $images,
            'properties' => $properties,
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

        $categories = $this->categoryRepository->findBy(['parent' => $category->getId()]);
        if ($categories) {
            $products = [];
            $arrayObject = [];
            foreach ($categories as $item) {
                $products[] = $item->getProducts();
            }
            foreach ($products as $product) {
                foreach ($product as $item) {
                    array_push($arrayObject, $item);
                }
            }

            $images = $this->searchFunctions->getImages($arrayObject, 3);

            $properties = [];
            foreach ($arrayObject as $product) {
                $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);
            }

            $pagination = $this->paginator->paginate($arrayObject, $pageRequest, 5);
            return $this->render('product/index.html.twig', [
                'categories' => $items,
                'pagination' => $pagination,
                'images' => $images,
                'properties' => $properties
            ]);
        }
        $products = $category->getProducts();

        $properties = [];
        foreach ($products as $product) {
            $properties[$product->getId()] = $this->propertyProductRepository->findBy(['product' => $product]);
        }
        $images = $this->searchFunctions->getImages($products, 3);

        $pagination = $this->paginator->paginate($products, $pageRequest, 5);
        return $this->render('product/index.html.twig', [
            'categories' => $items,
            'pagination' => $pagination,
            'images' => $images,
            'properties' => $properties
        ]);
    }
}
