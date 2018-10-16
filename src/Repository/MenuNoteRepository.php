<?php

namespace App\Repository;

use App\Entity\MenuNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MenuNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuNote[]    findAll()
 * @method MenuNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuNoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MenuNote::class);
    }

//    /**
//     * @return MenuNote[] Returns an array of MenuNote objects
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
    public function findOneBySomeField($value): ?MenuNote
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
