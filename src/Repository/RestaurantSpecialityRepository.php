<?php

namespace App\Repository;

use App\Entity\RestaurantSpeciality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RestaurantSpeciality|null find($id, $lockMode = null, $lockVersion = null)
 * @method RestaurantSpeciality|null findOneBy(array $criteria, array $orderBy = null)
 * @method RestaurantSpeciality[]    findAll()
 * @method RestaurantSpeciality[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantSpecialityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RestaurantSpeciality::class);
    }

//    /**
//     * @return RestaurantSpeciality[] Returns an array of RestaurantSpeciality objects
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
    public function findOneBySomeField($value): ?RestaurantSpeciality
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
