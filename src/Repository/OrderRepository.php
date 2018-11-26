<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Order::class);
    }

//    /**
//     * @return Order[] Returns an array of Order objects
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
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function getOrders($limit, $page, $status, $count=false)
    {
        $select = $this->createQueryBuilder('e');
        
        if($status)
            $select = $select->andWhere ('e.orderStatus = :cat')->setParameter ('cat', $status);
        
        if($count)
            return $select->select('COUNT(e)')->getQuery()->getSingleScalarResult();
        
        if($limit)
            $select = $select->setFirstResult( ($page-1)*$limit )->setMaxResults( $limit );
        
        
        
               
        return $select->getQuery()->getResult();
        
    }
    
    
    public function getUserOrders($user, $limit, $page, $status, $count=false)
    {
        $select = $this->createQueryBuilder('o');
        
        $select = $select->Join('App\Entity\Restaurant', 'r', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = o.restaurant');
        $select = $select->Join('App\Entity\User', 'u', \Doctrine\ORM\Query\Expr\Join::WITH, 'u.id = r.owner');
        
        $select = $select->andWhere('r.owner = :u')->setParameter('u', $user);
        
        if($status){
            if(!is_array($status)){
                $select = $select->andWhere ('o.orderStatus = :cat')->setParameter ('cat', $status);
            }else{
                $select = $select->andWhere ('o.orderStatus IN (:cat)')->setParameter ('cat', array_values($status));
            }
        }
        
        if($count)
            return $select->select('COUNT(o)')->getQuery()->getSingleScalarResult();
        
        if($limit)
            $select = $select->setFirstResult( ($page-1)*$limit )->setMaxResults( $limit );
        
        $select->orderBy('o.id', 'desc');
        
//        if($status)
//            $select = $select->andWhere ('o.orderStatus = :cat')->setParameter ('cat', $status);
//        
//        if($count)
//            return $select->select('COUNT(o)')->getQuery()->getSingleScalarResult();
//        
//        if($limit)
//            $select = $select->setFirstResult( ($page-1)*$limit )->setMaxResults( $limit );
        
        
        
        return $select->getQuery()->getResult();
        
    }
    
}
