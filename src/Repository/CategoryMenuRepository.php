<?php

namespace App\Repository;

use App\Entity\CategoryMenu;
use App\Entity\Menu;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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
    
    public function getCategories($limit, $page, $count=false){
        if($count){
            return $this->createQueryBuilder('e')->select('COUNT(e)')->getQuery()->getSingleScalarResult();
        }else{
            $qb=$this->createQueryBuilder('e')->select('e')    
               ->setFirstResult( ($page-1)*$limit )
               ->setMaxResults( $limit );
            return $qb->getQuery()->getResult();            
        }
    }
    
    public function getMenus($category, $limit, $page, $count = false, $distance, $longitude, $latitude){
        
        if(is_null($latitude))
            $latitude = 48.864716;
        if(is_null($longitude))
            $longitude = 2.349014;
        
        $rsm = new ResultSetMapping();
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Menu', 'm');
        
        $query = $this->_em->createNativeQuery(
            'SELECT m.*, ( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( r.latidude ) ) * cos( radians( r.longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( r.latidude ) ) ) ) AS distance '
            . 'FROM menu m '
            . 'JOIN restaurant r ON r.id = m.restaurant_id'
            .' WHERE 1 '
            . ' AND r.status = 1' 
            . ' HAVING distance <= '.$distance
            .' ORDER BY distance asc '
            .($limit ? ' LIMIT '.(($page - 1) * $limit).", $limit" : '')
            , $rsm
        );
        
        if($count) {
            $query = $this->_em->createNativeQuery(
                'SELECT m.*, ( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( r.latidude ) ) * cos( radians( r.longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( r.latidude ) ) ) ) AS distance '
                . 'FROM menu m '
                . 'JOIN restaurant r ON r.id = m.restaurant_id'
                .' WHERE 1 '
                . ' AND r.status = 1' 
                . ' HAVING distance <= '.$distance
                .' ORDER BY distance asc '
                .($limit ? ' LIMIT '.(($page - 1) * $limit).", $limit" : '')
                , $rsm
            );
            
            return count($query->getResult());
        }
        

        return $query->getResult();
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
