<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Statistic;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }



    public function tmpCount()
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT p.id, count(*) FROM product p 
            left join statistic s on s.product_id = p.id
            group by p.id";
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
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
    }
    public function getVisitBrandStore($stores, $manufacturers, $category, $dateFirst, $dateSecond): array
    {
        $builder = $this->createQueryBuilder('p')
            ->select('s2.nameStore', 'm.name', 'count(s) as count')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.statistics', 's')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.manufacturer', 'm')
            ->leftJoin('ai.store', 's2')
            ->where('c.name = :category')
            ->setParameter('category', $category)
            ->andWhere('m.name IN(:manufacturers)')
            ->setParameter('manufacturers', $manufacturers)
            ->andWhere('s2.nameStore IN(:stores)')
            ->setParameter('stores', $stores);
        if ($dateFirst !== "" && $dateSecond !== "") {
            $dateOne = new \DateTime($dateFirst);
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit BETWEEN :dateFirst and :dateSecond')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'))
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        } elseif ($dateFirst !== "" && $dateSecond === "") {
            $dateOne = new \DateTime($dateFirst);
            $builder->andWhere('s.dateVisit >= :dateFirst')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'));
        } elseif ($dateFirst === "" && $dateSecond !== "") {
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit <= :dateSecond')
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        }
        $builder->groupBy('m.name', 's2.nameStore');
        return $builder->getQuery()->getResult();
    }
    public function getVisitBrandProduct($manufacturers, $category, $dateFirst, $dateSecond): array
    {
        $builder = $this->createQueryBuilder('p')
            ->select('m.name', 'count(s) as count')
            ->leftJoin('p.statistics', 's')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.manufacturer', 'm')
            ->where('c.name = :category')
            ->setParameter('category', $category)
            ->andWhere('m.name IN(:manufacturers)')
            ->setParameter('manufacturers', $manufacturers)
            ->andWhere('s.additionalInfo is NULL');
        if ($dateFirst !== "" && $dateSecond !== "") {
            $dateOne = new \DateTime($dateFirst);
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit BETWEEN :dateFirst and :dateSecond')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'))
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        } elseif ($dateFirst !== "" && $dateSecond === "") {
            $dateOne = new \DateTime($dateFirst);
            $builder->andWhere('s.dateVisit >= :dateFirst')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'));
        } elseif ($dateFirst === "" && $dateSecond !== "") {
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit <= :dateSecond')
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        }
        $builder->groupBy('m.name');
        return $builder->getQuery()->getResult();
    }



    public function getStoresProduct(Product $product): array
    {
        $builder = $this->createQueryBuilder('p')
            ->distinct("true")
            ->select("s.nameStore", "ai.id")
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.store', 's')
            ->where('p.id=:id')
            ->setParameter("id", $product->getId());
        return $builder->getQuery()->getResult();
    }

    public function getRatingProductStore($stores, $productId): array
    {
        $builder = $this->createQueryBuilder('p')
            ->select('s.nameStore', 'avg(r.evaluation) as avg')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.ratings', 'r')
            ->leftJoin('ai.store', 's')
            ->where('p.id = :productId')
            ->setParameter('productId', (int) $productId)
            ->andWhere('s.nameStore IN(:stores)')
            ->setParameter('stores', $stores)
            ->groupBy('s.nameStore');
        return $builder->getQuery()->getResult();
    }

    public function getVisitProduct($dateFirst, $dateSecond, $productId): array
    {
        $builder = $this->createQueryBuilder('p')
            ->select('count(s) as count')
            ->leftJoin('p.statistics', 's')
            ->where('p.id = :productId')
            ->setParameter('productId', (int) $productId)
            ->andWhere('s.additionalInfo is NULL');
        if ($dateFirst !== "" && $dateSecond !== "") {
            $dateOne = new \DateTime($dateFirst);
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit BETWEEN :dateFirst and :dateSecond')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'))
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        } elseif ($dateFirst !== "" && $dateSecond === "") {
            $dateOne = new \DateTime($dateFirst);
            $builder->andWhere('s.dateVisit >= :dateFirst')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'));
        } elseif ($dateFirst === "" && $dateSecond !== "") {
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit <= :dateSecond')
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        }
        return $builder->getQuery()->getResult();
    }
    public function getVisitProductStore($stores, $dateFirst, $dateSecond, $productId): array
    {
        $builder = $this->createQueryBuilder('p')
            ->select('s2.nameStore', 'count(s) as count')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.statistics', 's')
            ->leftJoin('ai.store', 's2')
            ->where('p.id = :productId')
            ->setParameter('productId', (int) $productId)
            ->andWhere('s2.nameStore IN(:stores)')
            ->setParameter('stores', $stores);
        if ($dateFirst !== "" && $dateSecond !== "") {
            $dateOne = new \DateTime($dateFirst);
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit BETWEEN :dateFirst and :dateSecond')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'))
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        } elseif ($dateFirst !== "" && $dateSecond === "") {
            $dateOne = new \DateTime($dateFirst);
            $builder->andWhere('s.dateVisit >= :dateFirst')
                ->setParameter('dateFirst', $dateOne->format('Y-m-d H:i:s'));
        } elseif ($dateFirst === "" && $dateSecond !== "") {
            $dateTwo = new \DateTime($dateSecond);
            $builder->andWhere('s.dateVisit <= :dateSecond')
                ->setParameter('dateSecond', $dateTwo->format('Y-m-d H:i:s'));
        }
        $builder->groupBy('s2.nameStore');
        return $builder->getQuery()->getResult();
    }
    public function getComments($productId): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "select c.id, c.customer_id, c.additional_info_id, c.response_id, c.text, c.date,
                    u.name, u.avatar
                    from product as p
                    left join additional_info ai on p.id = ai.product_id
                    left join comment c on ai.id = c.additional_info_id
                    left join users u on c.customer_id = u.id
                    where p.id = $productId
                    and c.status = 'complete'
                    order by c.date DESC";
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
   /* public function getStoresByProductId($productId): array
    {
        $builder = $this->createQueryBuilder('p')
            ->distinct()
            ->select('s.id', 's.nameStore')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.store', 's')
            ->where('p.id = :id')
            ->setParameter('id', $productId);
        return $builder->getQuery()->getResult();
    }*/
   /* public function getOriginalCommentCurrentUser(Product $product, Users $user)
    {
        $builder=$this->createQueryBuilder('p')
            ->select('c.response')
            ->leftJoin('p.additionalInfos', 'ai')
            ->leftJoin('ai.comments', 'c')
            ->where('p.id = :id')
            ->setParameter('id', $product->getId())
            ->andWhere('c.customer = :user')
            ->setParameter('user', $user)
            ->andWhere('c.response is null');
        return $builder->getQuery()->getResult();

    }*/
}
