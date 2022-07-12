<?php

namespace App\DataFixtures;

use App\Entity\AdditionalInfo;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\PropertyProduct;
use App\Entity\Rating;
use App\Entity\SourceGoods;
use App\Entity\Statistic;
use App\Entity\Store;
use App\Entity\Users;
use App\Repository\AdditionalInfoRepository;
use Cassandra\Date;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use http\Exception\InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\Types\Self_;
use ProxyManager\Exception\FileNotWritableException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const DEMO_DATA_URL = 'http://b12.skillum.ru/bitrix/catalog_export/intarocrm.xml';

    private const UPLOAD_DIR = __DIR__ . '/../../public/upload/pictures';

    private const OFFERS_COUNT = 500;
    private $repository;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher, AdditionalInfoRepository $repository)
    {
        $this->passwordHasher = $passwordHasher;
        $this->repository=$repository;
    }

    public function load(ObjectManager $manager): void
    {
        //Создание пользователя
        $user = new Users();
        $user->setEmail('testEmail@emal.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $user->setRoles(['ROLE_CLIENT']);
        $user->setAvatar('avatar/img.png');
        $user->setGender('male');
        $user->setName('Kocmo');
        $manager->persist($user);
        $manager->flush();

        $user2 = new Users();
        $user2->setEmail('test2Email@emal.com');
        $user2->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $user2->setRoles(['ROLE_CLIENT']);
        $user2->setAvatar('avatar/img.png');
        $user2->setGender('male');
        $user2->setName('Kocmonavtik');
        $manager->persist($user2);
        $manager->flush();

        //Создание магазина
        $store = new Store();
        $store->setCustomer($user);
        $store->setNameStore('Market');
        $store->setDescription('Маркетплейс');
        $store->setLogo('storeLogo/img.png');
        $store->setUrlStore('none');
        $manager->persist($store);
        $manager->flush();

        $store1 = new Store();
        $store1->setCustomer($user2);
        $store1->setNameStore('MarketShop');
        $store1->setDescription('Магазин одежды');
        $store1->setLogo('storeLogo/img.png');
        $store1->setUrlStore('none');
        $manager->persist($store1);
        $manager->flush();

        $store2 = new Store();
        $store2->setCustomer($user2);
        $store2->setNameStore('ShopHouse');
        $store2->setDescription('Shopping house');
        $store2->setLogo('storeLogo/img.png');
        $store2->setUrlStore('none');
        $manager->persist($store2);
        $manager->flush();


        //Создание источника данных
        $sourceGoods = new SourceGoods();
        $sourceGoods->setCustomer($user);
        $sourceGoods->setStore($store);
        $sourceGoods->setUrl('http://b12.skillum.ru/bitrix/catalog_export/intarocrm.xml');
        $manager->persist($sourceGoods);
        $manager->flush();

        $output = new ConsoleOutput();
        $simpleXml = simplexml_load_string(file_get_contents(self::DEMO_DATA_URL));
        $output->writeln('xml loaded successfully');
        if (empty($simpleXml)) {
            throw new InvalidArgumentException('Unable to load xml from URL ' . self::DEMO_DATA_URL);
        }
        $fileSystem = new Filesystem();
        if (!is_writable(self::UPLOAD_DIR)) {
            throw new FileNotWritableException('Upload directory is not writable. Check file permissions');
        }
        $fileSystem->remove(glob(self::UPLOAD_DIR . '/*'));
        $output->writeln('upload directory cleaned');


        //Запись категорий
        $xmlCategories = $simpleXml->shop->categories->category;
        $categories = [];
        foreach ($xmlCategories as $section) {
            $category = new Category();
            $category->setName(trim((string) $section));
            $xmlId = (string) $section->attributes()->id ?? null;
            $categories[$xmlId] = $category;
            $manager->persist($category);
        }
        // Запись родителей категорий
        foreach ($xmlCategories as $xmlCategory) {
            $parentXmlId = (string) $xmlCategory->attributes()->parentId ?? null;
            if (!$parentXmlId) {
                continue;
            }
            $xmlId = (string) $xmlCategory->attributes()->id ?? null;
            $parent = $categories[$parentXmlId];
            $category = $categories[$xmlId];
            $category->setParent($parent);
            $manager->persist($category);
        }
        $output->writeln('categories processed');

        $xmlOffers = $simpleXml->shop->offers->offer;
        $productCount = 0;
        $products = [];
        $manufacturers = [];
        $additionalInfos = [];
        //$properties = [];
        $Property[] = new PropertyProduct();
        $output->writeln('offers processing...');
        $progresBar = new ProgressBar($output, min(count($xmlOffers), self::OFFERS_COUNT));

        //Запись товаров
        foreach ($xmlOffers as $xmlOffer) {
            if (++$productCount > self::OFFERS_COUNT) {
                break;
            }
            if (!$xmlOffer->param) {
                continue;
            }
            $offerXmlId = (string) $xmlOffer->attributes()->id;
            $productXmlId = (string) $xmlOffer->attributes()->productId;
            $categoryXmlId = (string) $xmlOffer->categoryId;
            $vendorXmlName = (string) $xmlOffer->vendor;
            //$paramXml = (string) $xmlOffer->param;

            //Запись в производителя
            if (!isset($manufacturers[$vendorXmlName])) {
                $manufacturer = new Manufacturer();
                $tmp = htmlspecialchars_decode($vendorXmlName);
                if (preg_match('/"([^"]+)"/', $tmp, $m)) {
                    $manufacturer->setName($m[1]);
                } else {
                    $manufacturer->setName($vendorXmlName);
                }
                $manager->persist($manufacturer);
                $manufacturers[$vendorXmlName] = $manufacturer;
            } else {
                $manufacturer = $manufacturers[$vendorXmlName];
            }
            //Запись в товар
            if (!isset($products[$productXmlId])) {
                $product = new Product();
                $product->setName((string) $xmlOffer->name);
                $product->setManufacturer($manufacturer);
                $product->addCategory($categories[(string)$xmlOffer->categoryId]);
                $manager->persist($product);
                $products[$productXmlId] = $product;


                //Работа с характеристиками
                //$stack = array();
                foreach ($xmlOffer->param as $xmlParam) {
                    /*$stack[(string) $xmlParam->attributes()->name] = (string)$xmlParam;*/
                    //if (!in_array((string)$xmlParam->attributes()->name, $properties, false)) {
                    if (empty($Property[(string)$xmlParam->attributes()->name])) {
                        //$properties[(string)$xmlParam->attributes()->name] = (string)$xmlParam->attributes()->name;
                        //$properties[(string)$xmlParam->attributes()->name][1] = (string)$xmlParam;
                        $item = new \App\Entity\Property();
                        $item->setName((string)$xmlParam->attributes()->name);
                        $Property[(string)$xmlParam->attributes()->name] = $item;
                        $manager->persist($item);
                        $propertyProduct = new PropertyProduct();
                        $propertyProduct->setProduct($product);
                        $propertyProduct->setProperty($item);
                        $propertyProduct->setValue((string)$xmlParam);
                        $manager->persist($propertyProduct);
                    } else {
                        $propertyProduct = new PropertyProduct();
                        $propertyProduct->setProduct($product);
                        $propertyProduct->setProperty($Property[(string)$xmlParam->attributes()->name]);
                        $propertyProduct->setValue((string)$xmlParam);
                        $manager->persist($propertyProduct);
                    }
                }
                //var_dump($properties);

                //$jsonParameter = json_encode($stack, JSON_UNESCAPED_UNICODE);
               // $items=json_decode($jsonParameter);;
               /* foreach($items as $item){
                    //var_dump(key($item));
                    var_dump($item);
                }*/
                /*$product->setParameter($stack);*/
               /* $manager->persist($product);
                $products[$productXmlId] = $product;*/
                //var_dump($properties);
                /*foreach ($properties as $property) {
                    //var_dump($property);
                    $item = new \App\Entity\Property();
                    $item->setName($property[0]);
                    $manager->persist($item);
                    $propertyProduct = new PropertyProduct();
                    $propertyProduct->setProduct($product);
                    $propertyProduct->setProperty($item);
                    $propertyProduct->setValue($property[1]);
                    $manager->persist($propertyProduct);
                }*/
            } else {
                $product = $products[$productXmlId];
            }
            //Запись в дополнительную информацию
            //if (!isset($additionalInfos[$productXmlId])) {
                $additionalInfo = new AdditionalInfo();
                $additionalInfo->setUrl((string)$xmlOffer->url);

                $randStore = mt_rand(1, 3);
            switch ($randStore) {
                case 1:
                    $additionalInfo->setStore($store);
                    break;
                case 2:
                    $additionalInfo->setStore($store1);
                    break;
                case 3:
                    $additionalInfo->setStore($store2);
                    break;
            }

                //$additionalInfo->setStore($store);
                $additionalInfo->setAverageRating(0);
                $additionalInfo->setDateUpdate(new \DateTime('now'));
                $additionalInfo->setPrice((float)$xmlOffer->price + mt_rand(100, 1500));
                $additionalInfo->setProduct($product);
                $additionalInfo->setStatus('complete');
                $stack = (string)$this->savePicture((string)$xmlOffer->picture);
                //var_dump($stack);
                //$jsonImages = json_encode($stack);
                $additionalInfo->setImage([$stack]);

                $manager->persist($additionalInfo);
                $comment = new Comment();
                $comment->setCustomer($user);
                $comment->setAdditionalInfo($additionalInfo);
                $comment->setDate(new \DateTime('now'));
                $comment->setText('Тестовый отзыв');
                $manager->persist($comment);
                $responseComment = new Comment();
                $responseComment->setText('Согласен, это тестовый отзыв');
                $responseComment->setDate(new \DateTime('now'));
                $responseComment->setAdditionalInfo($additionalInfo);
                $responseComment->setResponse($comment);
                $responseComment->setCustomer($user);
                $manager->persist($responseComment);

                $rating1 = new Rating();
                $rating1->setAdditionalInfo($additionalInfo);
                $rating1->setCustomer($user);
                $rating1->setEvaluation(mt_rand(1, 5));
                $rating1->setDate(new \DateTime('now'));
                $manager->persist($rating1);

                $rating2 = new Rating();
                $rating2->setAdditionalInfo($additionalInfo);
                $rating2->setCustomer($user2);
                $rating2->setEvaluation(mt_rand(1, 5));
                $rating2->setDate(new \DateTime('now'));
                $manager->persist($rating2);
                $additionalInfo->setAverageRating(($rating2->getEvaluation() + $rating1->getEvaluation()) / 2);
                $manager->persist($additionalInfo);



                //$additionalInfos[$productXmlId] = $additionalInfo;
            //} else {

                //$additionalInfo = $additionalInfos[$productXmlId];
            //}
            $progresBar->advance();
        }
        $progresBar->finish();
        $output->writeln('');
        $output->writeln('Flush to database...');
        $manager->flush();
        $output->writeln('Flush to database finished');

        $additionalInfos= $this->repository->findAll();
        foreach ($additionalInfos as $additionalInfo){
            $rndStatistic = random_int(100, 300);
            for($i=0; $i<$rndStatistic; ++$i){
                $statistic= new Statistic();
                $statistic->setAdditionalInfo($additionalInfo);
                $statistic->setDateVisit(new \DateTime('now'));
                $manager->persist($statistic);
            }
            $manager->flush();
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
    }
}
