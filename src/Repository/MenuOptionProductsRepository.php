<?php

namespace App\Repository;

use App\Entity\MenuOptionProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MenuOptionProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuOptionProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuOptionProducts[]    findAll()
 * @method MenuOptionProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuOptionProductsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MenuOptionProducts::class);
    }

//    /**
//     * @return MenuOptionProducts[] Returns an array of MenuOptionProducts objects
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
    public function findOneBySomeField($value): ?MenuOptionProducts
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
