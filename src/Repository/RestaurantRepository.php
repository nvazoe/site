<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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


    public function getRestaurants($longitude, $latitude, $status, $limit, $page, $count = false, $distance = 30) {
        
        if(is_null($latitude))
            $latitude = 48.864716;
        if(is_null($longitude))
            $longitude = 2.349014;
        //$longitude = $latitude = null;
        $rsm = new ResultSetMapping();
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Restaurant', 'r');
        
        $query = $this->_em->createNativeQuery('SELECT r.*, ( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( latidude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latidude ) ) ) ) AS distance '
            . 'FROM restaurant r '
            .' WHERE 1 '
            .($status ? ' AND r.status = '.$status : '')
            . ' HAVING distance <= '.$distance
            .' ORDER BY distance asc '
            .($limit ? ' LIMIT '.(($page - 1) * $limit).", $limit" : '')
            , $rsm
        );
        
        //$query->addSelect('( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latitude ) ) ) ) AS distance');

        if($count) {
            $query = $this->_em->createNativeQuery('SELECT r.*, ( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( latidude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latidude ) ) ) ) AS distance '
                . 'FROM restaurant r '
                .' WHERE 1 '
                .($status ? ' AND r.status = '.$status : '')
                . ' HAVING distance <= '.$distance
                .' ORDER BY distance asc '
                , $rsm
            );
            return count($query->getResult());
        }
            
        return $query->getResult();
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
