<?php

namespace App\Repository;

use App\Entity\DeliveryDisponibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DeliveryDisponibility|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryDisponibility|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryDisponibility[]    findAll()
 * @method DeliveryDisponibility[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryDisponibilityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeliveryDisponibility::class);
    }

//    /**
//     * @return DeliveryDisponibility[] Returns an array of DeliveryDisponibility objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeliveryDisponibility
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
