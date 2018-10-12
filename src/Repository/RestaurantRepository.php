<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Restaurant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restaurant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restaurant[]    findAll()
 * @method Restaurant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

//    /**
//     * @return Restaurant[] Returns an array of Restaurant objects
//     */
    
    
    public function getRestaurants($limit, $page, $count=false)
    {
        if($count){
            return $this->createQueryBuilder('e')->select('COUNT(e)')->getQuery()->getSingleScalarResult();
        }else{
            $qb=$this->createQueryBuilder('e')->select('e')    
               ->setFirstResult( ($page-1)*$limit )
               ->setMaxResults( $limit );
            return $qb->getQuery()->getResult();            
        }
    }
    
    
    

    /*
    public function findOneBySomeField($value): ?Restaurant
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
