<?php

namespace App\Repository;

use App\Entity\MenuMenuOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MenuMenuOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuMenuOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuMenuOption[]    findAll()
 * @method MenuMenuOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuMenuOptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MenuMenuOption::class);
    }

//    /**
//     * @return MenuMenuOption[] Returns an array of MenuMenuOption objects
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
    public function findOneBySomeField($value): ?MenuMenuOption
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
