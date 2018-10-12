<?php

namespace App\Repository;

use App\Entity\TypeMenu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TypeMenu|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeMenu|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeMenu[]    findAll()
 * @method TypeMenu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeMenuRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TypeMenu::class);
    }

//    /**
//     * @return TypeMenu[] Returns an array of TypeMenu objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypeMenu
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
