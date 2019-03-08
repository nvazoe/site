<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Menu::class);
    }

//    /**
//     * @return Menu[] Returns an array of Menu objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function findByRestau($restau, $limit, $page, $category, $count = false)
    {
        //die(var_dump($restau.' '.$category));
        $select = $this->createQueryBuilder('m');
        if($category)
            $select = $select->andWhere ('m.categoryMenu = :cat')->setParameter('cat', $category);
        
        if($restau)
            $select = $select->andWhere('m.restaurant = :val')->setParameter('val', $restau);
        
        if($count)
            return $select->select('COUNT(m)')->getQuery()->getSingleScalarResult();
        
        if($limit)
            $select = $select->setFirstResult( ($page-1)*$limit )->setMaxResults($limit);
        
        
        
//        $select = $select->groupBy('m.categoryMenu');
        
        
        
        return $select->getQuery()->getResult();
    }
    
    public function getMenus($limit, $page, $count=false, $distance = 30, $longitude, $latitude)
    {
        if(is_null($latitude))
            $latitude = 48.864716;
        if(is_null($longitude))
            $longitude = 2.349014;
        
        
        $select = $this->createQueryBuilder('e');
        $select->Join('App\Entity\Restaurant', 'r', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = e.restaurant');
        $select->select('e.*, r.( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( latidude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latidude ) ) ) ) AS distance');
        $select->andWhere('r.status = :u')->setParameter('u', 1);
        $select->having('distance <= :distance')->setParameter('distance', $distance);
        if($count){
            return $select->select('COUNT(e)')->getQuery()->getSingleScalarResult();
        }else{
               
            $select->setFirstResult( ($page-1)*$limit )
               ->setMaxResults( $limit );
            return $select->getSql();            
        }
    }
}
