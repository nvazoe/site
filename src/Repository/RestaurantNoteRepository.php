<?php

namespace App\Repository;

use App\Entity\RestaurantNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RestaurantNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method RestaurantNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method RestaurantNote[]    findAll()
 * @method RestaurantNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantNoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RestaurantNote::class);
    }

//    /**
//     * @return RestaurantNote[] Returns an array of RestaurantNote objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RestaurantNote
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
