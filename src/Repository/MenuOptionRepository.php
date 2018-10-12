<?php

namespace App\Repository;

use App\Entity\MenuOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MenuOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuOption[]    findAll()
 * @method MenuOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuOptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MenuOption::class);
    }

//    /**
//     * @return MenuOption[] Returns an array of MenuOption objects
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
    public function findOneBySomeField($value): ?MenuOption
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
