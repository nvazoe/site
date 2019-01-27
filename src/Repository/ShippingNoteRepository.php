<?php

namespace App\Repository;

use App\Entity\ShippingNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShippingNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingNote[]    findAll()
 * @method ShippingNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingNoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShippingNote::class);
    }

    // /**
    //  * @return ShippingNote[] Returns an array of ShippingNote objects
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
    public function findOneBySomeField($value): ?ShippingNote
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function getShippingByDeliver($deliver){
        $query = $this->createQueryBuilder('s');
        $query->join('App\Entity\Order', 'o', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.id = s.command');
        $query->andWhere('o.messenger = :stat')->setParameter('stat', $deliver);
        return $query->getQuery()->getResult();
    }
    
    public function getShippingByRestaurant($restaurant){
        $query = $this->createQueryBuilder('s');
        $query->join('App\Entity\Order', 'o', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.id = s.command');
        $query->andWhere('o.restaurant = :stat')->setParameter('stat', $restaurant);
        return $query->getQuery()->getResult();
    }
}
