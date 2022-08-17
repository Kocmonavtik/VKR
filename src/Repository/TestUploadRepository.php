<?php

namespace App\Repository;

use App\Entity\TestUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TestUpload>
 *
 * @method TestUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestUpload[]    findAll()
 * @method TestUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestUploadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestUpload::class);
    }

    public function add(TestUpload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TestUpload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getDistinctManufacturers()
    {
        return $this->createQueryBuilder('t')
            ->select('t.manufacturer')
            ->distinct(true)
            ->getQuery()
            ->getResult();
    }
    public function getDistinctCategories()
    {
        return $this->createQueryBuilder('t')
            ->select('t.category')
            ->distinct('true')
            ->getQuery()
            ->getResult();
    }
    public function getDistinctStores()
    {
        return $this->createQueryBuilder('t')
            ->select('t.store')
            ->distinct('true')
            ->getQuery()
            ->getResult();
    }
    public function getProperties(){
        return $this->createQueryBuilder('t')
            ->select('t.property')
            ->getQuery()
            ->getResult();
    }

    public function removeAll()
    {
        return $this->createQueryBuilder('t')
            ->delete()
            ->getQuery()
            ->execute();
    }

//    /**
//     * @return TestUpload[] Returns an array of TestUpload objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TestUpload
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
