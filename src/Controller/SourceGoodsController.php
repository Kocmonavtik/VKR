<?php

namespace App\Controller;

use App\Entity\AdditionalInfo;
use App\Entity\Category;
use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\Property;
use App\Entity\PropertyProduct;
use App\Entity\SourceGoods;
use App\Entity\Store;
use App\Entity\TestUpload;
use App\Form\SourceGoodsType;
use App\Repository\SourceGoodsRepository;
use App\Repository\StoreRepository;
use App\Repository\TestUploadRepository;
use App\Service\SearchFunctions;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use ProxyManager\Exception\FileNotWritableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/source")
 */
class SourceGoodsController extends AbstractController
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/upload/pictures';
    private SearchFunctions $searchFunctions;
    private StoreRepository $storeRepository;
    private ManagerRegistry $doctrine;
    private PaginatorInterface $paginator;
    private TestUploadRepository $testUploadRepository;
    public function __construct(
        SearchFunctions $searchFunctions,
        StoreRepository $storeRepository,
        ManagerRegistry $doctrine,
        PaginatorInterface $paginator,
        TestUploadRepository $testUploadRepository
    ) {
        $this->searchFunctions = $searchFunctions;
        $this->storeRepository = $storeRepository;
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->testUploadRepository = $testUploadRepository;
    }
    /**
     * @Route("/", name="app_source_goods_index", methods={"GET"})
     */
    public function index(SourceGoodsRepository $sourceGoodsRepository): Response
    {
        $items = $this->searchFunctions->getCategories();
        $manager = $this->doctrine->getManager();
        $processingSource = $manager->getRepository(SourceGoods::class)->findBy(['status' => 'processing']);
        $isProcessing = false;
        if (!empty($processingSource)) {
            $isProcessing = true;
        }
        return $this->render('source_goods/index.html.twig', [
            'sources' => $sourceGoodsRepository->findAll(),
            'categories' => $items,
            'isProcessing' => $isProcessing
        ]);
    }

    /**
     * @Route("/load", name="app_source_load", methods={"GET", "POST"})
     */
    public function load(Request $request): JsonResponse
    {
        return $this->json([
            'code' => 200,
        ]);
    }
    /**
     * @Route("/loadTest", name="app_source_load_test", methods={"GET", "POST"})
     */
    public function testLoad(Request $request): JsonResponse
    {
        return $this->json([
            'code' => 200,
        ]);
    }

    /**
     * @Route("/test", name="app_source_goods_test", methods={"GET"})
     */
    public function testSource(Request $request): Response
    {
        $pageRequest = $request->query->getInt('page', 1);
        if ($pageRequest <= 0) {
            $pageRequest = 1;
        }
        $products = $this->testUploadRepository->findAll();
        $pagination = $this->paginator->paginate($products, $pageRequest, 10);
        $manufacturers = $this->testUploadRepository->getDistinctManufacturers();
        $categories = $this->testUploadRepository->getDistinctCategories();
        $store = $this->testUploadRepository->getDistinctStores();
        $properties = $this->testUploadRepository->getProperties();
        /*var_dump($property[0]['property']);*/
        $distinctProperties = [];
        foreach ($properties as $propertyMas) {
            foreach ($propertyMas['property'] as $key => $value) {
                $tmp = preg_replace('/\d/', '', $key);
                $distinctProperties[$tmp][$value] = $value;
            }
        }
        return $this->render('source_goods/indexTest.html.twig', [
            'pagination' => $pagination,
            'manufacturers' => $manufacturers,
            'categoriesTest' => $categories,
            'store' => $store,
            'properties' => $distinctProperties,
        ]);
    }

    /**
     * @Route("/new", name="app_source_goods_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SourceGoodsRepository $sourceGoodsRepository): Response
    {
        $items = $this->searchFunctions->getCategories();
        $sourceGood = new SourceGoods();
        $form = $this->createForm(SourceGoodsType::class, $sourceGood);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sourceGood->setCustomer($this->getUser());
            $sourceGood->setStore($this->storeRepository->findAll()[0]);
            $sourceGoodsRepository->add($sourceGood, true);

            return $this->redirectToRoute('app_source_goods_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('source_goods/new.html.twig', [
            'source_good' => $sourceGood,
            'form' => $form,
            'categories' => $items
        ]);
    }

    /**
     * @Route("/{id}", name="app_source_goods_show", methods={"GET"})
     */
   /* public function show(SourceGoods $sourceGood): Response
    {
        $items = $this->searchFunctions->getCategories();
        return $this->render('source_goods/show.html.twig', [
            'source_good' => $sourceGood,
            'categories' => $items
        ]);
    }*/

    /**
     * @Route("/{id}/edit", name="app_source_goods_edit", methods={"GET", "POST"})
     */
    /*public function edit(Request $request, SourceGoods $sourceGood, SourceGoodsRepository $sourceGoodsRepository): Response
    {
        $items = $this->searchFunctions->getCategories();
        $form = $this->createForm(SourceGoodsType::class, $sourceGood);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sourceGoodsRepository->add($sourceGood, true);

            return $this->redirectToRoute('app_source_goods_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('source_goods/edit.html.twig', [
            'source_good' => $sourceGood,
            'form' => $form,
            'categories' => $items
        ]);
    }*/

    /**
     * @Route("/{id}", name="app_source_goods_delete", methods={"POST"})
     */
   /* public function delete(Request $request, SourceGoods $sourceGood, SourceGoodsRepository $sourceGoodsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sourceGood->getId(), $request->request->get('_token'))) {
            $sourceGoodsRepository->remove($sourceGood, true);
        }

        return $this->redirectToRoute('app_source_goods_index', [], Response::HTTP_SEE_OTHER);
    }*/
}
