<?php

namespace App\Repository;

use App\Entity\ConnexionLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ConnexionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConnexionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConnexionLog[]    findAll()
 * @method ConnexionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConnexionLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ConnexionLog::class);
    }

//    /**
//     * @return ConnexionLog[] Returns an array of ConnexionLog objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConnexionLog
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function getLastConnectRow($user){
        $query = $this->createQueryBuilder('c');
        
        if($user)
            $query = $query->andWhere ('c.user = :val')->setParameter('val', $user);
        
        $query->setMaxResults(1);
        $query->orderBy('c.id', 'desc');
        
        return $query->getQuery()->getSingleResult();
    }
    
    public function getUserLogs($user){
        $query = $this->createQueryBuilder('c');
        
        if($user)
            $query = $query->andWhere ('c.user = :val')->setParameter('val', $user);
        
        $query->andWhere('c.connectStatus = :cs')->setParameter('cs', 1);
        
        return $query->getQuery()->getResult();
    }
}
