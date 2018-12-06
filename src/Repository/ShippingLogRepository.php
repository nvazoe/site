<?php

namespace App\Repository;

use App\Entity\ShippingLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShippingLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingLog[]    findAll()
 * @method ShippingLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShippingLog::class);
    }

    // /**
    //  * @return ShippingLog[] Returns an array of ShippingLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShippingLog
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function getDeliverActionBetweenAPeriod($del, $time1, $time2){
        //die(var_dump('T1: '.$time1.' - T2 :'.$time2));
        $query = $this->createQueryBuilder('s');
        if($del)
            $query->andWhere ('s.messenger = :msgr')->setParameter ('msgr', $del);
        if($time1 && $time2)
            $query->andWhere ('s.makeAt BETWEEN :t2 AND :t1')->setParameter ('t1', $time1)->setParameter ('t2', $time2);
        
        //echo '<pre>'; die(var_dump($query->getDQL())); echo '<pre>';
        return $query->getQuery()->getResult();
    }
}
