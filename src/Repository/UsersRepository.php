<?php

namespace App\Repository;

use App\Entity\Clients;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 *
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    /**
     * The number of desired results (limit).
     */
    public const RESULT_PER_PAGE = 3;


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

   /**
    * @return Users[] Returns an array of Users objects
    */
   public function findByClients(Clients $client): array
   {
       return $this->createQueryBuilder('u')
           ->andWhere('u.clients = :val')
           ->setParameter('val', $client)
           ->getQuery()
           ->getResult()
       ;
   }


   public function findByClientsWithPagination(Clients $client, int $offset = 0): array
   {
       return $this->createQueryBuilder('u')
           ->andWhere('u.clients = :val')
           ->setParameter('val', $client)
           ->orderBy('u.id', 'ASC')
           ->setMaxResults(self::RESULT_PER_PAGE)
           ->setFirstResult($offset)
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?Users
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
