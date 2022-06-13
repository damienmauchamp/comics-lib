<?php

namespace App\Repository;

use App\Entity\IssueBackup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueBackup>
 *
 * @method IssueBackup|null find($id, $lockMode = null, $lockVersion = null)
 * @method IssueBackup|null findOneBy(array $criteria, array $orderBy = null)
 * @method IssueBackup[]    findAll()
 * @method IssueBackup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueBackupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueBackup::class);
    }

    public function add(IssueBackup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(IssueBackup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return IssueBackup[] Returns an array of IssueBackup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?IssueBackup
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
