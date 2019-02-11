<?php

namespace App\Repository;

use App\Entity\CategoryMenu;
use App\Entity\Menu;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CategoryMenu|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryMenu|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryMenu[]    findAll()
 * @method CategoryMenu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryMenuRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CategoryMenu::class);
    }

//    /**
//     * @return CategoryMenu[] Returns an array of CategoryMenu objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategoryMenu
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function getCategories($limit, $page, $count=false)
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
    
    public function getMenus($category, $limit, $page, $count = false){
        $this->_entityName = Menu::class;
        $query = $this->createQueryBuilder('m');
        
        if($category)
            $query = $query->andWhere ('m.categoryMenu = :val')->setParameter ('val', $category);
        
        if($count)
            return $query->select('COUNT(m)')->getQuery()->getSingleScalarResult();
        
        if($limit)
            $query = $query->setFirstResult( ($page-1)*$limit )->setMaxResults( $limit );
        
        return $query->getQuery()->getResult();
    }
    
    public function getRestaurants($category, $limit, $page, $count = false){
        $this->_entityName = Restaurant::class;
        $query = $this->createQueryBuilder('r');
        $query->select('r');
        $query->distinct();
        $query->join('App\Entity\Menu', 'm', \Doctrine\ORM\Query\Expr\Join::WITH, 'm.restaurant = r.id');
        $query->join('App\Entity\categoryMenu', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'm.categoryMenu = c.id');
        $query->andWhere ('m.categoryMenu = :val')->setParameter ('val', $category);
        
        if($count)
            return $query->select('COUNT(m)')->getQuery()->getSingleScalarResult();
        
        if($limit)
            $query = $query->setFirstResult( ($page-1)*$limit )->setMaxResults( $limit );
        
        return $query->getQuery()->getResult();
    }
}
