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
        $counter = 3;
        $minPrice = $options['minPriceValue'];
        $maxPrice = $options['maxPriceValue'];
        $sort = $options['selectSort'];
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
            $counter++;
        }

        if (!empty($options['manufacturer'])) {
            $manufacturer = $options['manufacturer'];
            $counter++;
        }
        //новый
        $properties = [];
        if (count($options) - $counter !== 0) {
            //$property = [];
            $i = 0;
            $j = 0;
            while ($i < count($options) - $counter) {
                //$property = [];
                if (empty($options["property_$j"])) {
                    ++$j;
                } else {
                    foreach ($options["property_$j"] as $item) {
                        $properties[$i][] = mb_substr(strstr($item, '/'), 1);
                    }
                   /* $builder->andWhere("pp.value IN (:property$j)")
                        ->setParameter("property$j", $property);*/
                    ++$j;
                    ++$i;
                }
            }
        }
        $result = [];
        if (empty($properties)) {
            $builder = $this->createQueryBuilder('p')
                ->leftJoin('p.propertyProducts', 'pp')
                ->leftJoin('pp.property', 'prop')
                ->leftJoin('p.category', 'c')
                ->leftJoin('p.manufacturer', 'm')
                ->leftJoin('p.additionalInfos', 'ai')
                ->leftJoin('ai.ratings', 'r')
                ->where('ai.price between :min and :max')
                ->setParameter('min', $minPrice)
                ->setParameter('max', $maxPrice);
            if (!empty($category)) {
                $builder->andWhere('c.name IN (:category)')
                    ->setParameter('category', $category);
            }
            if (!empty($manufacturer)) {
                $builder->andWhere('m.name IN (:manufacturer)')
                    ->setParameter('manufacturer', $manufacturer);
            }

            $builder->groupBy('p.id');
            switch ($sort) {
                case 'rating':
                    $builder->orderBy('avg(r.evaluation)', 'DESC');
                    break;
                case 'priceUp':
                    $builder->orderBy('min(ai.price)', 'ASC');
                    break;
                case 'priceDown':
                    $builder->orderBy('min(ai.price)', 'DESC');
                    break;
            }
            return $builder->getQuery()->getResult();
        } else {
            foreach ($properties as $property) {
                $builder = $this->createQueryBuilder('p')
                   ->leftJoin('p.propertyProducts', 'pp')
                   ->leftJoin('pp.property', 'prop')
                   ->leftJoin('p.category', 'c')
                   ->leftJoin('p.manufacturer', 'm')
                   ->leftJoin('p.additionalInfos', 'ai')
                   ->leftJoin('ai.ratings', 'r')
                   ->where('ai.price between :min and :max')
                   ->setParameter('min', $minPrice)
                   ->setParameter('max', $maxPrice);
                if (!empty($category)) {
                    $builder->andWhere('c.name IN (:category)')
                       ->setParameter('category', $category);
                }
                if (!empty($manufacturer)) {
                    $builder->andWhere('m.name IN (:manufacturer)')
                       ->setParameter('manufacturer', $manufacturer);
                }

                $builder->andWhere("pp.value IN (:property)")
                   ->setParameter("property", $property);

                $builder->groupBy('p.id');
                switch ($sort) {
                    case 'rating':
                        $builder->orderBy('avg(r.evaluation)', 'DESC');
                        break;
                    case 'priceUp':
                        $builder->orderBy('min(ai.price)', 'ASC');
                        break;
                    case 'priceDown':
                        $builder->orderBy('min(ai.price)', 'DESC');
                        break;
                }
                $result[] = $builder->getQuery()->getResult();
            }
            $count = count($result);
            $countsCoincidences = [];
            $masObjects = [];
            foreach ($result as $products) {
                foreach ($products as $product) {
                    $masObjects[$product->getId()] = $product;
                    $countsCoincidences[] = $product->getId();
                }
            }
            $resultObjects = [];
            $counts = array_count_values($countsCoincidences);
            foreach ($counts as $key => $item) {
                if ($count === $counts[$key]) {
                    $resultObjects[] = $masObjects[$key];
                }
            }
            return $resultObjects;
        }



        //окончание
      /*  $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.propertyProducts', 'pp')
            ->leftJoin('pp.property', 'prop')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.manufacturer', 'm')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.ratings', 'r')
            ->where('ai.price between :min and :max')
            ->setParameter('min', $minPrice)
            ->setParameter('max', $maxPrice);
        if (!empty($category)) {
            $builder->andWhere('c.name IN (:category)')
                ->setParameter('category', $category);
        }
        if (!empty($manufacturer)) {
            $builder->andWhere('m.name IN (:manufacturer)')
                ->setParameter('manufacturer', $manufacturer);
        }

        if (count($options) - $counter !== 0) {
            $property = [];
            $i = 0;
            $j = 0;
            while ($i < count($options) - $counter) {
                $property = [];
                if (empty($options["property_$j"])) {
                    ++$j;
                } else {
                    //$key = stristr($options["property_$j"][0], '/', true);
                    foreach ($options["property_$j"] as $item) {
                        $property[] = mb_substr(strstr($item, '/'), 1);
                    }
                    $builder->andWhere("pp.value IN (:property$j)")
                        ->setParameter("property$j", $property);
                    ++$j;
                    ++$i;
                }
            }
        }
        $builder->groupBy('p.id');
        switch ($sort) {
            case 'rating':
                $builder->orderBy('avg(r.evaluation)', 'DESC');
                break;
            case 'priceUp':
                $builder->orderBy('min(ai.price)', 'ASC');
                break;
            case 'priceDown':
                $builder->orderBy('min(ai.price)', 'DESC');
                break;
        }*/
    }

    public function sortProductRating($search)
    {
        $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.ratings', 'r');
        if ($search !== null) {
            $builder->where('LOWER(p.name) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $search . '%');
        }
            $builder->groupBy('p.id')
            ->orderBy('avg(r.evaluation)', 'DESC');
        return $builder->getQuery()->getResult();
    }
    public function sortProductMinPrice($search)
    {
        $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai');
        if ($search !== null) {
            $builder->where('LOWER(p.name) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $search . '%');
        }
           $builder->groupBy('p.id')
            ->orderBy('min(ai.price)', 'DESC');
        return $builder->getQuery()->getResult();
    }
    public function sortProductMaxPrice($search)
    {
        $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai');
        if ($search !== null) {
            $builder->where('LOWER(p.name) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $search . '%');
        }
        $builder->groupBy('p.id')
            ->orderBy('min(ai.price)', 'ASC');
        return $builder->getQuery()->getResult();
    }
}
