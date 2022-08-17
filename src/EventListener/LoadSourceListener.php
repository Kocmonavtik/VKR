<?php

namespace App\EventListener;

use App\Entity\AdditionalInfo;
use App\Entity\Category;
use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\Property;
use App\Entity\PropertyProduct;
use App\Entity\SourceGoods;
use App\Entity\TestUpload;
use App\Repository\StoreRepository;
use App\Service\SearchFunctions;
use App\Service\ServiceRepository;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Exception\BaseException;
use ProxyManager\Exception\FileNotWritableException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Config\MonologConfig;

use const Grpc\STATUS_OUT_OF_RANGE;

class LoadSourceListener
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/upload/pictures';
    private const UPLOAD_TEST_DIR = __DIR__ . '/../../public/upload/test';
    private ManagerRegistry $doctrine;
    private LoggerInterface $logger;
    private MonologConfig $monolog;

    public function __construct(
        ManagerRegistry $doctrine,
        LoggerInterface $logger
    ) {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }
    public function onKernelTerminate(TerminateEvent $event): void
    {
        try {
            $this->logger->notice('Начало обработки Xml Файла');
            $request = $event->getRequest();
            $url = (string) $request->getUri();
            $schemaAndHttpHost = (string) $request->getSchemeAndHttpHost();
            //http://agregator.local:83/source/load?id=18
            //http://agregator.local:83
            //if()
            //$this->logger->error('Работает!' . $source->getUrl());
            if (stripos($url, $schemaAndHttpHost . '/source/load?id=') === false && stripos($url, $schemaAndHttpHost . '/source/loadTest?id=') === false) {
                $this->logger->notice('Проверка не пройдена');
                return;
            }
            $this->logger->notice('Пройдена проверка ссылки');
            $id = $request->query->get('id');
            $manager = $this->doctrine->getManager();
            $source = $manager->getRepository(SourceGoods::class)->find((int) $id);
            $sources = $manager->getRepository(SourceGoods::class)->findBy(['status' => 'processing']);
            if (!empty($sources)) {
                return;
            }
            $this->logger->notice('Пройдена проверка на запущенную обработку');
            $source->setStatus('processing');
            $manager->persist($source);
            $manager->flush();
            if (stripos($url, $schemaAndHttpHost . '/source/loadTest?id=') !== false) {
                $this->logger->notice('Началась тестовая загрузка данных');
                $sourcesTest = $manager->getRepository(SourceGoods::class)->findBy(['status' => 'testing']);
                if (!empty($sourcesTest)) {
                    foreach ($sourcesTest as $item) {
                        $item->setStatus('progress');
                        $manager->persist($item);
                        $manager->flush();
                    }
                }
                $manager->getRepository(TestUpload::class)->removeAll();
                $manager->flush();
                $fileSystem = new Filesystem();
                $fileSystem->remove(glob(self::UPLOAD_TEST_DIR . '/*'));
                $this->logger->notice('Очищено от старых данных');

                $result = $this->loadTestingSource($source);
                if ($result === true) {
                    $source->setStatus('testing');
                    $manager->persist($source);
                    $manager->flush();
                    $this->logger->info('Обработка Xml файла с id ' . $id . ' - успешно завершена');
                } else {
                    $source->setStatus('error');
                    $manager->persist($source);
                    $manager->flush();
                    $this->logger->warning('Ошибка при обработке файла' . $result);
                }
                return;
            } else {
                $result = $this->loadSource($source);
                if ($result === true) {
                    $source->setStatus('processed');
                    $manager->persist($source);
                    $manager->flush();
                    $this->logger->info('Обработка Xml файла с id ' . $id . ' - успешно завершена');
                } else {
                    $source->setStatus('error');
                    $manager->persist($source);
                    $manager->flush();
                    $this->logger->warning('Ошибка при обработке файла' . $result);
                }
                return;
            }
        } catch (\Exception $e) {
            $this->logger->error('Ошибка при обработке файла' . $e->getMessage());
            return;
        }

        //$this->logger->info('Работает! id источника:' . $source->getId());
    }

    private function loadTestingSource(SourceGoods $source)
    {
        try {
            $this->logger->notice('Перешел на функцию обработки тестового файла');
            $manager = $this->doctrine->getManager();
            $simpleXml = simplexml_load_string(file_get_contents($source->getUrl()));
            if (empty($simpleXml)) {
                throw new \InvalidArgumentException('Не удается загрузить XML с ссылки:' . $source->getUrl());
            }
            $fileSystem = new Filesystem();
            if (!is_writable(self::UPLOAD_TEST_DIR)) {
                throw new FileNotWritableException('Не права на запись. Настройте права директорий');
            }
            $xmlCategories = $simpleXml->shop->categories->category;
            $categories = [];
            $store = $source->getStore()->getNameStore();

            //Запись категорий;
            foreach ($xmlCategories as $section) {
                if ((int)$section->attributes()->id === 9) { //скип нижнего белья
                    continue;
                }
                $nameCategory = trim((string)$section);
                $xmlId = (string)$section->attributes()->id ?? null;
                $categories[$xmlId] = $nameCategory;
            }
            $this->logger->notice('Категории записаны');

            $xmlOffers = $simpleXml->shop->offers->offer;
            $products = [];
            $manufacturers = [];
            $additionalInfos = [];
            $Property = [];
            $Images = [];
            $this->logger->notice('Вхождение в цикл с товарами');

            foreach ($xmlOffers as $xmlOffer) {
                if (!$xmlOffer->param) {
                    continue;
                }

                if ((int)$xmlOffer->categoryId === 9) {
                    continue;
                }

                $offerXmlId = (string)$xmlOffer->attributes()->id;
                $productXmlId = (string)$xmlOffer->attributes()->productId;
                $categoryXmlId = (string)$xmlOffer->categoryId;
                $vendorXmlName = (string)$xmlOffer->vendor;
                if (empty($vendorXmlName)) {
                    $vendorXmlName = (string)$xmlOffer->brand;
                }
                if (empty($vendorXmlName)) {
                    $vendorXmlName = 'Не брендированный';
                }

                $this->logger->notice('xml id товара:' . $productXmlId);
                $this->logger->notice('Бренд:' . $vendorXmlName);

                //Запись производителя
                if ($vendorXmlName === 'Не брендированный') {
                    $name = $vendorXmlName;
                } else {
                    $tmpName = htmlspecialchars_decode($vendorXmlName);
                    if (preg_match('/"([^"]+)"/', $tmpName, $m)) {
                        $name = $m[1];
                    } else {
                        $name = $vendorXmlName;
                    }
                }
                $manufacturers[$productXmlId] = $name;
                $this->logger->notice('Пройдена запись производителя:' . $productXmlId);


                //запись товара
                if (!isset($products[$productXmlId])) {
                    $name = (string)$xmlOffer->name;
                    $product = new TestUpload();
                    $product->setName($name)
                        ->setManufacturer($manufacturers[$productXmlId])
                        ->setCategory($categories[(string) $xmlOffer->categoryId])
                        ->setStore($store)
                        ->setUrl((string)$xmlOffer->url)
                        ->setPrice((float)$xmlOffer->price);
                    $products[$productXmlId] = $product;

                    //Запись характеристик
                    foreach ($xmlOffer->param as $xmlParam) {
                        $name = (string) $xmlParam->attributes()->name;
                        $value = (string)$xmlParam;
                        $Property[$productXmlId][$name][$value] = $value;
                        if (empty($Property[$productXmlId][$name][$value])) {
                            $Property[$productXmlId][$name][$value] = $value;
                        }
                    }
                } else {
                    //Запись характеристик
                    foreach ($xmlOffer->param as $xmlParam) {
                        $name = (string) $xmlParam->attributes()->name;
                        $value = (string)$xmlParam;
                        $Property[$productXmlId][$name][$value] = $value;
                        if (empty($Property[$productXmlId][$name][$value])) {
                            $Property[$productXmlId][$name][$value] = $value;
                        }
                    }
                }
                //запись предложения
                $image = (string)$this->savePictureTest((string)$xmlOffer->picture);
                $Images[$productXmlId][] = $image;
            }
            foreach ($products as $key => $product) {
                $product->setImage($Images[$key]);
                $properties = [];
                foreach ($Property[$key] as $keyName => $values) {
                    $i = 0;
                    foreach ($values as $value) {
                        $properties[(string) $keyName . $i] = $value;
                        $i++;
                    }
                }
                $product->setProperty($properties);
                $manager->persist($product);
            }
            $manager->flush();
            $this->logger->notice('Закончено прохождение товаров');
            return true;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    private function loadSource(SourceGoods $source)
    {
        try {
            $this->logger->notice('Перешел на функцию обработки файла');
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
            $this->logger->notice('Категории записаны');
            $xmlOffers = $simpleXml->shop->offers->offer;
            $products = [];
            $manufacturers = [];
            $additionalInfos = [];
            $Property[] = new PropertyProduct();
            $this->logger->notice('Вхождение в цикл с товарами');

            foreach ($xmlOffers as $xmlOffer) {
                if (!$xmlOffer->param) {
                    continue;
                }
                if ((int)$xmlOffer->categoryId === 9) {
                    continue;
                }
                $offerXmlId = (string)$xmlOffer->attributes()->id;
                $productXmlId = (string)$xmlOffer->attributes()->productId;
                $categoryXmlId = (string)$xmlOffer->categoryId;
                $vendorXmlName = (string)$xmlOffer->vendor;
                if (empty($vendorXmlName)) {
                    $vendorXmlName = (string)$xmlOffer->brand;
                }
                if (empty($vendorXmlName)) {
                    $vendorXmlName = 'Не брендированный';
                }

                $this->logger->notice('xml id товара:' . $offerXmlId);
                $this->logger->notice('Бренд:' . $vendorXmlName);

                //Запись производителя
                if (!isset($manufacturers[$vendorXmlName])) {
                    if ($vendorXmlName === 'Не брендированный') {
                        $name = $vendorXmlName;
                    } else {
                        $tmpName = htmlspecialchars_decode($vendorXmlName);
                        if (preg_match('/"([^"]+)"/', $tmpName, $m)) {
                            $name = $m[1];
                        } else {
                            $name = $vendorXmlName;
                        }
                    }
                    $manufacturer = $manager->getRepository(Manufacturer::class)->findOneBy(['name' => $name]);
                    if (empty($manufacturer)) {
                        $manufacturer = new Manufacturer();
                        $manufacturer->setName($name);
                        $manager->persist($manufacturer);
                    }
                    $manufacturers[$vendorXmlName] = $manufacturer;
                } else {
                    $manufacturer = $manufacturers[$vendorXmlName];
                }
                $this->logger->notice('Пройдена запись производителя:' . $offerXmlId);

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
                                    'property' => $property,
                                    'product' => $product
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
                                    'property' => $Property[$name],
                                    'product' => $product,
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
                                    'property' => $property,
                                    'product' => $product
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
                                    'property' => $Property[$name],
                                    'product' => $product
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
                    $manager->flush();
                } else {
                    $additionalInfo->setDateUpdate(new \DateTime('now'))
                        ->setPrice((float)$xmlOffer->price)
                        ->setUrl((string)$xmlOffer->url);
                    $manager->persist($additionalInfo);
                    $manager->flush();
                }
            }
            $manager->flush();
            $this->logger->notice('Закончено прохождение товаров');
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    private function savePicture(string $pictureUrl): ?string
    {
        $this->logger->notice('Сохранение картинки');
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
    }

    private function savePictureTest(string $pictureUrl): ?string
    {
        $this->logger->notice('Сохранение картинки');
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
        if (!$filesystem->exists(self::UPLOAD_TEST_DIR . '/' . $dir)) {
            $filesystem->mkdir(self::UPLOAD_TEST_DIR . '/' . $dir);
        }
        try {
            $filesystem->rename($tempName, self::UPLOAD_TEST_DIR . '/' . $dir . '/' . $newFileName);
            $filesystem->chmod(self::UPLOAD_TEST_DIR . '/' . $dir . '/' . $newFileName, 0755);
        } catch (\Exception $exception) {
            return null;
        }
        return $dir . '/' . $newFileName;
    }
}
