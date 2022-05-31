<?php

namespace App\DataFixtures;

use App\Entity\AdditionalInfo;
use App\Entity\Category;
use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\SourceGoods;
use App\Entity\Store;
use App\Entity\Users;
use Cassandra\Date;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use http\Exception\InvalidArgumentException;
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

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new Users();
        $user->setEmail('testEmail@emal.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $user->setRoles(['ROLE_CLIENT']);
        $user->setAvatar('/../../public/upload/avatar/img.png');
        $user->setGender('male');
        $user->setName('Kocmo');
        $manager->persist($user);
        $manager->flush();

        $store = new Store();
        $store->setCustomer($user);
        $store->setNameStore('Market');
        $store->setDescription('Маркетплейс');
        $store->setLogo('storeLogo/img.png');
        $store->setUrlStore('none');
        $manager->persist($store);
        $manager->flush();

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
        $output->writeln('offers processing...');
        $progresBar = new ProgressBar($output, min(count($xmlOffers), self::OFFERS_COUNT));

        foreach ($xmlOffers as $xmlOffer) {
            if (++$productCount > self::OFFERS_COUNT) {
                break;
            }
            if(!$xmlOffer->param){
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
                $stack = array();
                foreach ($xmlOffer->param as $xmlParam) {
                    //$code= (string) $xmlParam->attributes()->code
                    // ?: \Transliterator::create('tr_Lower')->transliterate($xmlParam->attributes()->name);
                    $stack[(string) $xmlParam->attributes()->name] = (string)$xmlParam;
                }
                //$jsonParameter = json_encode($stack, JSON_UNESCAPED_UNICODE);
               // $items=json_decode($jsonParameter);;
               /* foreach($items as $item){
                    //var_dump(key($item));
                    var_dump($item);
                }*/
                $product->setParameter($stack);
                $manager->persist($product);
                $products[$productXmlId] = $product;
            } else {
                $product = $products[$productXmlId];
            }
            //Запись в дополнительную информацию
            //if (!isset($additionalInfos[$productXmlId])) {
                $additionalInfo = new AdditionalInfo();
                $additionalInfo->setUrl((string)$xmlOffer->url);
                $additionalInfo->setStore($store);
                $additionalInfo->setAverageRating(0);
                $additionalInfo->setDateUpdate(new \DateTime('now'));
                $additionalInfo->setPrice((float)$xmlOffer->price);
                $additionalInfo->setProduct($product);
                $additionalInfo->setStatus('complete');
                $stack = (string)$this->savePicture((string)$xmlOffer->picture);
                //var_dump($stack);
                //$jsonImages = json_encode($stack);
                $additionalInfo->setImage([$stack]);
                $manager->persist($additionalInfo);
                $additionalInfos[$productXmlId] = $additionalInfo;
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
