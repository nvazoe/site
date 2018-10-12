<?php

namespace App\Repository;

use App\Entity\OrderMenuProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderMenuProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderMenuProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderMenuProduct[]    findAll()
 * @method OrderMenuProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderMenuProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderMenuProduct::class);
    }

//    /**
//     * @return OrderMenuProduct[] Returns an array of OrderMenuProduct objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderMenuProduct
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
