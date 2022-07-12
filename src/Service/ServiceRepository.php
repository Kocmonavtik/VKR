<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getAverageRatingAndMinPrice(): array
    {
        $conection = $this->getEntityManager()->getConnection();

        $sql =
            'SELECT ai.product_id, avg(r.evaluation), min(ai.price) FROM additional_info ai
               left join rating r on ai.id = r.additional_info_id
               group by ai.product_id';
        $stmt = $conection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        /*$resultSet = $stmt->executeQuery(['price' => $price]);*/
        return $resultSet->fetchAllAssociative();
    }
    public function getCategories(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'WITH tmp as (
    SELECT c.id, c.parent_id, c.name, count(pc.product_id)  as countProd from category as c
    left join product_category pc on c.id = pc.category_id
    group by c.id
)
select tmp.id, tmp.parent_id, tmp.name from tmp
where tmp.countProd > 0';
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    public function getManufacturers(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * from manufacturer';
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    public function getSores(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * from store';
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    public function getManufacturersCategory(string $category): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "select distinct m.id, m.name from manufacturer as m
            left join product p on m.id = p.manufacturer_id
            left join product_category pc on p.id = pc.product_id
            left join category c on pc.category_id = c.id
            where c.name='" . $category . "'";
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    public function getStoresCategory(string $category): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "select distinct s.id, s.customer_id, s.name_store, s.url_store, s.logo, s.description from store as s
            left join additional_info ai on s.id = ai.store_id
            left join product p on ai.product_id = p.id
            left join product_category pc on p.id = pc.product_id
            left join category c on pc.category_id = c.id
            where c.name='" . $category . "'";
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    public function getRatingBrandStore($stores, $manufacturers, $category): array
    {
        /*$builder = $this->createQueryBuilder('p')
            ->distinct('true')
            ->leftJoin('p.propertyProducts', 'pp')
            ->leftJoin('pp.property', 'prop')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.manufacturer', 'm')
            ->leftJoin('p.additionalInfos', 'ai')
            ->where('ai.price between :min and :max')
            ->setParameter('min', $minPrice)
            ->setParameter('max', $maxPrice);

        $builder = $this->createQueryBuilder('p')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.ratings', 'r')
            ->groupBy('p.id')
            ->orderBy('avg(r.evaluation)', 'DESC');
        return $builder->getQuery()->getResult();*/

        $builder = $this->createQueryBuilder('p')
            ->select('s.nameStore', 'm.name', 'avg(r.evaluation) as avg')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.ratings', 'r')
            ->leftJoin('ai.store', 's')
            ->leftJoin('p.manufacturer', 'm')
            ->leftJoin('p.category', 'c')
            ->where('c.name = :category')
            ->setParameter('category', $category)
            ->andWhere('m.name IN(:manufacturers)')
            ->setParameter('manufacturers', $manufacturers)
            ->andWhere('s.nameStore IN(:stores)')
            ->setParameter('stores', $stores)
            ->groupBy('m.name , s.nameStore');
        return $builder->getQuery()->getResult();


       /* $connection = $this->getEntityManager()->getConnection();
        $sql = "select m.name, s.name_store, avg(r.evaluation) from rating as r
            left join additional_info ai on ai.id = r.additional_info_id
            left join store s on ai.store_id = s.id
            left join product p on ai.product_id = p.id
            left join manufacturer m on p.manufacturer_id = m.id
            left join product_category pc on p.id = pc.product_id
            left join category c on pc.category_id = c.id
            where c.name='$category'
            and m.name in($manufacturers)
            and s.name_store in($stores)
            group by m.name, s.name_store";
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();*/
    }


   /* public function getProductWithMinPrice(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'select p.id, min(ai.price) from product p
                    left join additional_info ai on p.id = ai.product_id
                    group by p.id';
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }*/
   /* public function getAverageRatingProduct(Product $product): array
    {
        $conection = $this->getEntityManager()->getConnection();

        $sql =
            'SELECT ai.product_id, avg(r.evaluation) FROM additional_info ai
               left join rating r on ai.id = r.additional_info_id
               group by ai.product_id';
        $stmt = $conection->prepare($sql);
        $resultSet = $stmt->executeQuery();*/
        /*$resultSet = $stmt->executeQuery(['price' => $price]);*/
       /* return $resultSet->fetchAllAssociative();
    }*/
}
