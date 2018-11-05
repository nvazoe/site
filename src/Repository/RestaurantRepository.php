<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Restaurant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restaurant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restaurant[]    findAll()
 * @method Restaurant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Restaurant::class);
    }

//    /**
//     * @return Restaurant[] Returns an array of Restaurant objects
//     */


    public function getRestaurants($longitude, $latitude, $status, $limit, $page, $count = false) {
        
        $longitude = $latitude = null;
        
        $query = $this->createQueryBuilder('r');
        
        if($longitude)
            $query = $query->andWhere ('r.longitude = :val1')->setParameter ('val1', $longitude);
        if($latitude)
            $query = $query->andWhere ('r.latidude = :val2')->setParameter ('val2', $latitude);
        if($status)
            $query = $query->andWhere ('r.status = :val3')->setParameter ('val3', $status);
        if($count)
            return $query->select('COUNT(r)')->getQuery()->getSingleScalarResult();
        if($limit)
            $query = $query->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        
        
        return $query->getQuery()->getResult();
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

    public function getOrders($restaurant, $status, $limit, $page, $count = false) {
        $this->_entityName = Order::class;
        $select = $this->createQueryBuilder('o');

        if ($restaurant)
            $select = $select->andWhere('o.restaurant = :cat')->setParameter('cat', $restaurant);
        
        if ($status)
            $select = $select->andWhere('o.restaurant = :stat')->setParameter('stat', $status);
        
        if($count)
            return $select->select('COUNT(o)')->getQuery()->getSingleScalarResult();
        
        if($limit)
            $select = $select->setFirstResult( ($page-1)*$limit )->setMaxResults($limit);
        
        
        
        
        return $select->getQuery()->getResult();
    }

}
