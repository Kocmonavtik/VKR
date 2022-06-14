<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Stmt\While_;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private $categoryRepository;
    private $manufacturerRepository;
    public function __construct(ManagerRegistry $registry, CategoryRepository $categoryRepository, ManufacturerRepository $manufacturerRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function search(string $query)
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.name) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
    public function getProductsAdditionalsInfo()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai')
            ->getQuery()
            ->getResult();
    }
    public function getProductsWithFilter(array $options, $origCategory)
    {

        $minPrice = $options['minPriceValue'];
        $maxPrice = $options['maxPriceValue'];
        if (empty($options['category'])) {
            $categories = $this->categoryRepository->findBy(['parent' => $origCategory->getId()]);
            if ($categories) {
                $category = [];
                foreach ($categories as $item) {
                    $category[] = $item->getName();
                }
            } else {
                $category = $origCategory->getName();
            }
        } else {
            $category = $options['category'];
        }

        $counter = 3;
        if (!empty($options['manufacturer'])) {
            $manufacturer = $options['manufacturer'];
            $counter++;
        }

        $property = [];
        $i = 0;
        $j = 0;
        while ($i < count($options) - $counter) {
            if (empty($options["property_$j"])) {
                ++$j;
                continue;
            }
            $key = stristr($options["property_$j"][0], '/', true);
            foreach ($options["property_$j"] as $item) {
                $property[] = mb_substr(strstr($item, '/'), 1);
            }
            ++$j;
            ++$i;
        }
        $builder = $this->createQueryBuilder('p')
            ->distinct('true')
            ->leftJoin('p.propertyProducts', 'pp')
            ->leftJoin('pp.property', 'prop')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.manufacturer', 'm')
            ->leftJoin('p.additionalInfos', 'ai')
            ->where('ai.price between :min and :max')
            ->setParameter('min', $minPrice)
            ->setParameter('max', $maxPrice);
        if (!empty($category)) {
            $builder->andWhere('c.name IN (:category)')
                ->setParameter('category', $category);
        }
        if (!empty($manufacturer)) {
            $builder->andWhere('m.name IN (:manifacturer)')
                ->setParameter('manifacturer', $manufacturer);
        }
        if (!empty($property)) {
            $builder->andWhere('pp.value IN (:property)')
                ->setParameter('property', $property);
        }

        //var_dump($property);
       /* $masQueries=[];
        foreach ($property as $key=>$properties){
            $builder=$this->createQueryBuilder('p')
                ->leftJoin('p.propertyProducts', 'pp')
                ->leftJoin('pp.property','property')
                ->where('property.name= :key')
                ->setParameter('key',$key);
            foreach ($properties as $item){*/
               /* $builder->orWhere("c.id = :id$i");
                $builder->setParameter("id$i", $this->categoryRepository->findOneBy(['name' => $category[$i]])->getId());
                $builder->andWhere()*/
       /*     }

        }
        $rangePriceProducts = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai')
            ->where('ai.price > :minprice')
            ->setParameter('minprice', $minPrice)
            ->andWhere('ai.price < :maxprice')
            ->setParameter('maxprice', $maxPrice)
            ->getQuery()
            ->getResult();
        if (empty($options['manufacturer'])) {
        } elseif (count($manufacturer) > 1) {
            $builder = $this->createQueryBuilder('p')
                ->leftJoin('p.manufacturer', 'm')
                ->where('m.name = :name0')
                ->setParameter('name0', $this->manufacturerRepository->findOneBy(['name' => $manufacturer])->getName());
            for ($i = 1, $iMax = count($manufacturer); $i < $iMax; ++$i) {
                $builder->orWhere("m.name = :name$i");
                $builder->setParameter("name$i", $this->manufacturerRepository->findOneBy(['name' => $manufacturer[$i]])->getName());
            }
            $manufacturerProducts = $builder->getQuery()->getResult();
        } else {
            $manufacturerProducts = $this->createQueryBuilder('p')
                ->leftJoin('p.manufacturer', 'm')
                ->where('m.name = :name')
                ->setParameter('name', $this->manufacturerRepository->findOneBy(['name' => $manufacturer])->getName())
                ->getQuery()
                ->getResult();
        }


        if (empty($options['category'])) {
            $categories = $this->categoryRepository->findBy(['parent' => $origCategory->getId()]);
            if ($categories) {
                $categoryProducts = [];
                $arrayObject = [];
                foreach ($categories as $item) {
                    $arrayObject[] = $item->getProducts();
                }
                foreach ($arrayObject as $product) {
                    foreach ($product as $item) {
                        array_push($categoryProducts, $item);
                    }
                }
            } else {
                $categoryProducts=[];
                $tmp = $origCategory->getProducts();
                foreach ($tmp as $item){
                    array_push($categoryProducts, $item);
                }
            }
        }

        if (empty($manufacturer)) {
            $resultForm = array_uintersect($rangePriceProducts, $categoryProducts, function ($a, $b) {
                return strcmp(spl_object_hash($a), spl_object_hash($b));
            });
        } else {
            $resultForm = array_uintersect($rangePriceProducts, $manufacturerProducts, function ($a, $b) {
                return strcmp(spl_object_hash($a), spl_object_hash($b));
            });
            $resultForm = array_uintersect($resultForm, $categoryProducts, function ($a, $b) {
                return strcmp(spl_object_hash($a), spl_object_hash($b));
            });
        }*/
        return $builder->getQuery()->getResult();
    }
    public function sortProductRating(){

    }
    public function sortProductMinPrice(){

    }
    public function sortProductMaxPrice(){

    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
