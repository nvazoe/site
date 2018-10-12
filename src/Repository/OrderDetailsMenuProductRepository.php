<?php

namespace App\Repository;

use App\Entity\OrderDetailsMenuProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderDetailsMenuProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderDetailsMenuProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderDetailsMenuProduct[]    findAll()
 * @method OrderDetailsMenuProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderDetailsMenuProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderDetailsMenuProduct::class);
    }

//    /**
//     * @return OrderDetailsMenuProduct[] Returns an array of OrderDetailsMenuProduct objects
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
    public function findOneBySomeField($value): ?OrderDetailsMenuProduct
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
