<?php

namespace App\Repository;

use App\Entity\ShippingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShippingStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingStatus[]    findAll()
 * @method ShippingStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShippingStatus::class);
    }

//    /**
//     * @return ShippingStatus[] Returns an array of ShippingStatus objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShippingStatus
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
