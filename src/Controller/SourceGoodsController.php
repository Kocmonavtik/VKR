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
use App\Form\SourceGoodsType;
use App\Repository\SourceGoodsRepository;
use App\Repository\StoreRepository;
use App\Service\SearchFunctions;
use Doctrine\Persistence\ManagerRegistry;
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
    public function __construct(
        SearchFunctions $searchFunctions,
        StoreRepository $storeRepository,
        ManagerRegistry $doctrine
    ) {
        $this->searchFunctions = $searchFunctions;
        $this->storeRepository = $storeRepository;
        $this->doctrine = $doctrine;
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
        /*$string = (string)$request->getUri();
        var_dump($request->getSchemeAndHttpHost());*/
        //$id = $request->query->get('id');
      /*  $manager = $this->doctrine->getManager();
        $source = $manager->getRepository(SourceGoods::class)->find((int) $id);*/
       /* try {
            $source->setStatus('processing');
            $manager->persist($source);
            $manager->flush();
            //$id = $source->getId();
            //$result = $this->loadSource($source);
            $code = 200;
        } catch (\Exception $e) {
            $code = 500;
        }*/
        return $this->json([
            'code' => 200,
        ]);
    }

   /* private function loadSource(SourceGoods $source)
    {
        try {
            $manager = $this->doctrine->getManager();
            $simpleXml = simplexml_load_string(file_get_contents($source->getUrl()));
            if (empty($simpleXml)) {
                throw new \InvalidArgumentException('Не удается загрузить XML с ссылки:' . $source->getUrl());
            }
            $fileSystem = new Filesystem();
            if (!is_writable(self::UPLOAD_DIR)) {
                throw new FileNotWritableException('Не права на запись. Настройте права директорий');
            }
            $xmlCategories = $simpleXml->shop->categories->category;
            $categories = [];


            $store = $source->getStore();

            //Запись категорий;
            foreach ($xmlCategories as $section) {
                if ((int)$section->attributes()->id === 9) { //скип нижнего белья
                    continue;
                }
                $nameCategory = trim((string)$section);
                $xmlId = (string)$section->attributes()->id ?? null;
                $category = $manager->getRepository(Category::class)->findOneBy(['name' => $nameCategory]);
                if (empty($category)) {
                    $category = new Category();
                    $category->setName($nameCategory);
                    $categories[$xmlId] = $category;
                    $manager->persist($category);
                } else {
                    $categories[$xmlId] = $category;
                }
            }
            //Запись подкатегорий;
            foreach ($xmlCategories as $xmlCategory) {
                $parentXmlId = (string)$xmlCategory->attributes()->parentId ?? null;
                if (!$parentXmlId) {
                    continue;
                }
                $xmlId = (string)$xmlCategory->attributes()->id ?? null;
                $parent = $categories[$parentXmlId];
                $category = $categories[$xmlId];
                $category->setParent($parent);
                $manager->persist($category);
            }
            $xmlOffers = $simpleXml->shop->offers->offer;
            $products = [];
            $manufacturers = [];
            $additionalInfos = [];
            $Property[] = new PropertyProduct();

            foreach ($xmlOffers as $xmlOffer) {
                if (!$xmlOffers->param) {
                    continue;
                }
                if ((int)$xmlOffer->categoryId === 9) {
                    continue;
                }
                $offerXmlId = (string)$xmlOffer->attributes()->id;
                $productXmlId = (string)$xmlOffer->attributes()->productId;
                $categoryXmlId = (string)$xmlOffer->categoryId;
                $vendorXmlName = (string)$xmlOffer->vendor;

                //Запись производителя
                if (!isset($manufacturers[$vendorXmlName])) {
                    $tmpName = htmlspecialchars_decode($vendorXmlName);
                    if (preg_match('/"([^"]+)"/', $tmpName, $m)) {
                        $name = $m[1];
                    } else {
                        $name = $vendorXmlName;
                    }
                    $manufacturer = $manager->getRepository(Manufacturer::class)->findOneBy(['name' => $m[1]]);
                    if (empty($manufacturer)) {
                        $manufacturer = new Manufacturer();
                        $manufacturer->setName($name);
                        $manager->persist($manufacturer);
                    }
                    $manufacturers[$vendorXmlName] = $manufacturer;
                } else {
                    $manufacturer = $manufacturers[$vendorXmlName];
                }

                //запись товара
                if (!isset($products[$productXmlId])) {
                    $name = (string)$xmlOffer->name;
                    $product = $manager->getRepository(Product::class)->findOneBy(['name' => $name]);
                    if (empty($product)) {
                        $product = new Product();
                        $product->setName($name)
                            ->setManufacturer($manufacturer)
                            ->addCategory($categories[(string)$xmlOffer->categoryId]);
                        $manager->persist($product);
                    }
                    $products[$productXmlId] = $product;

                    //Запись характеристик
                    foreach ($xmlOffer->param as $xmlParam) {
                        $name = (string)$xmlParam->attributes()->name;
                        if (empty($Property[$name])) {
                            $property = $manager->getRepository(Property::class)->findOneBy(['name' => $name]);
                            if (empty($property)) {
                                $property = new Property();
                                $property->setName($name);
                                $manager->persist($property);
                            }
                            $Property[$name] = $property;

                            //запись значений характеристик
                            $value = (string)$xmlParam;
                            $propertyProduct = $manager->getRepository(PropertyProduct::class)->findOneBy(
                                [
                                    'value' => $value,
                                    'property' => $property
                                ]
                            );
                            if (empty($propertyProduct)) {
                                $propertyProduct = new PropertyProduct();
                                $propertyProduct->setProduct($product)
                                    ->setValue($value)
                                    ->setProperty($property);
                                $manager->persist($propertyProduct);
                            }
                        } else {
                            //запись значений характеристик
                            $value = (string)$xmlParam;
                            $propertyProduct = $manager->getRepository(PropertyProduct::class)->findOneBy(
                                [
                                    'value' => $value,
                                    'property' => $Property[$name]
                                ]
                            );
                            if (empty($propertyProduct)) {
                                $propertyProduct = new PropertyProduct();
                                $propertyProduct->setProduct($product)
                                    ->setValue($value)
                                    ->setProperty($Property[$name]);
                                $manager->persist($propertyProduct);
                            }
                        }
                    }
                } else {
                    $product = $products[$productXmlId];
                }
                //запись предложения
                $additionalInfo = $manager->getRepository(AdditionalInfo::class)->findOneBy(
                    [
                        'product' => $product,
                        'store' => $store,
                    ]
                );
                if (empty($additionalInfo)) {
                    $image = (string)$this->savePicture((string)$xmlOffer->picture);
                    $additionalInfo = new AdditionalInfo();
                    $additionalInfo->setProduct($product)
                        ->setStatus('complete')
                        ->setUrl((string)$xmlOffer->url)
                        ->setStore($store)
                        ->setAverageRating(0)
                        ->setPrice((float)$xmlOffer->price)
                        ->setDateUpdate(new \DateTime('now'))
                        ->setImage([$image]);
                    $manager->persist($additionalInfo);
                } else {
                    $additionalInfo->setDateUpdate(new \DateTime('now'))
                        ->setPrice((float)$xmlOffer->price)
                        ->setUrl((string)$xmlOffer->url);
                    $manager->persist($additionalInfo);
                }
            }
            $manager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    private function savePicture(string $pictureUrl): ?string
    {
        $filesystem = new Filesystem();
        if (empty($pictureUrl)) {
            return null;
        }
        $fileContent = file_get_contents($pictureUrl);
        if (empty($fileContent)) {
            return null;
        }
        $tempName = $filesystem->tempnam('/tmp', 'offer_picture_');
        $filesystem->dumpFile($tempName, $fileContent);

        $fileData = pathinfo($pictureUrl);
        $file = new UploadedFile($tempName, $fileData['basename']);
        if ('jpg' !== $file->guessExtension() || $file->getSize() > 10 * 1024 * 1024) {
            return null;
        }
        $newFileName = sha1($pictureUrl . uniqid('', true)) . '.jpg';
        $dir = substr($newFileName, 0, 2);
        if (!$filesystem->exists(self::UPLOAD_DIR . '/' . $dir)) {
            $filesystem->mkdir(self::UPLOAD_DIR . '/' . $dir);
        }
        try {
            $filesystem->rename($tempName, self::UPLOAD_DIR . '/' . $dir . '/' . $newFileName);
            $filesystem->chmod(self::UPLOAD_DIR . '/' . $dir . '/' . $newFileName, 0755);
        } catch (\Exception $exception) {
            return null;
        }
        return $dir . '/' . $newFileName;
    }*/


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
