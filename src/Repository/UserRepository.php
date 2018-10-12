<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    /**
     * {@inheritdoc}
     */
    public function findAllUserByRole($role, $get_qb = true)
    {
        $qb = $this->getBuilder();
        //$qb->leftJoin('u.groups', 'g')
            $qb->where($qb->expr()->orX(
                $qb->expr()->like('u.roles', ':roles')
                //$qb->expr()->like('g.roles', ':roles')
            ))
            ->setParameter('roles', '%"'.$role.'"%');
        if($get_qb)
            return $qb;

        return $qb->getQuery()->getResult();
    }
    
    /**
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBuilder($alias = 'u')
    {
        return $this->createQueryBuilder($alias);
    }
    
//    public function countByRoleCustom($role){
//        $qb = $this->findAllUserByRole($role);
//        return $qb->getQuery()->getSingleScalarResult();
//    }
}
