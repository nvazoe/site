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
    
    public function findByRestau($restau, $limit, $page, $count = false)
    {
        if($count){
            return $this->createQueryBuilder('m')
            ->andWhere('m.restaurant = :val')
            ->setParameter('val', $restau)
            ->select('COUNT(m)')
            ->getQuery()
            ->getSingleScalarResult();
        }
        
        return $this->createQueryBuilder('m')
            ->andWhere('m.restaurant = :val')
            ->setParameter('val', $restau)
            ->orderBy('m.id', 'DESC')
            ->setFirstResult( ($page-1)*$limit )
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getMenus($limit, $page, $count=false)
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
}
