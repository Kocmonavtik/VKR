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
        $counter = 2;
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
            $counter++;
        }

        if (!empty($options['manufacturer'])) {
            $manufacturer = $options['manufacturer'];
            $counter++;
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
        return $builder->getQuery()->getResult();
    }

    public function sortProductRating()
    {
        $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.ratings', 'r')
            ->groupBy('p.id')
            ->orderBy('avg(r.evaluation)', 'DESC');
        return $builder->getQuery()->getResult();
    }
    public function sortProductMinPrice()
    {
        $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai')
            ->groupBy('p.id')
            ->orderBy('min(ai.price)', 'DESC');
        return $builder->getQuery()->getResult();
    }
    public function sortProductMaxPrice()
    {
        $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai')
            ->groupBy('p.id')
            ->orderBy('min(ai.price)', 'ASC');
        return $builder->getQuery()->getResult();
    }

}
