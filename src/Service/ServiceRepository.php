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
