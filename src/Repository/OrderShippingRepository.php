<?php

namespace App\Repository;

use App\Entity\OrderShipping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderShipping|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderShipping|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderShipping[]    findAll()
 * @method OrderShipping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderShippingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderShipping::class);
    }

//    /**
//     * @return OrderShipping[] Returns an array of OrderShipping objects
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
    public function findOneBySomeField($value): ?OrderShipping
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
