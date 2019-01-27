<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function findByNameField($value, $user=null)
    {
        $query = $this->createQueryBuilder('p');
        
        if($user){
            $query->join('App\Entity\Restaurant', 'r', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = p.restaurant');
            $query->join('App\Entity\User', 'u',\Doctrine\ORM\Query\Expr\Join::WITH, 'u.id = r.owner');
            $query->andWhere('u.id = :user')->setParameter('user', $user);
        }
        $query->andWhere('p.name LIKE :val')->setParameter('val', '%'.$value.'%');
        $query->andWhere('p.status LIKE :stat')->setParameter('stat', 1);
        $query->orderBy('p.id', 'DESC');
        return $query->getQuery()->getResult();
    }
    
    public function getProducts($limit, $page, $count=false)
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
