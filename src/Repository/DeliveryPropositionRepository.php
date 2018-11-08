<?php

namespace App\Repository;

use App\Entity\DeliveryProposition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DeliveryProposition|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryProposition|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryProposition[]    findAll()
 * @method DeliveryProposition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryPropositionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeliveryProposition::class);
    }

//    /**
//     * @return DeliveryProposition[] Returns an array of DeliveryProposition objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeliveryProposition
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function getOrders($deliver, $limit, $page, $count=false){
        $query = $this->createQueryBuilder('d');

        if($deliver)
            $query = $query->andWhere('d.deliver = :del')->setParameter('del', $deliver);
        
        $query = $query->andWhere('d.valueDeliver = :v')->setParameter('v', 0);
        
        if($count)
            return $query->select('COUNT(d)')->getQuery()->getSingleScalarResult();
        
        if($limit)
            $query = $query->setFirstResult( ($page-1)*$limit )->setMaxResults($limit);
        
        return $query->getQuery()->getResult();
    }
    
    
    public function getOrderRow($deliver, $order){
        $query = $this->createQueryBuilder('d');
        
        if($deliver)
            $query = $query->andWhere('d.deliver = :del')->setParameter('del', $deliver);
        
        if($order)
            $query = $query->andWhere('d.command = :ord')->setParameter('ord', $order);
        
        return $query->getQuery()->getOneOrNullResult();
        
    }
}
