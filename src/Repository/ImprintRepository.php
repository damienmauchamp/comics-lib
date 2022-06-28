<?php

namespace App\Repository;

use App\Entity\Imprint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Imprint>
 *
 * @method Imprint|null find($id, $lockMode = null, $lockVersion = null)
 * @method Imprint|null findOneBy(array $criteria, array $orderBy = null)
 * @method Imprint[]    findAll()
 * @method Imprint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImprintRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Imprint::class);
	}

	public function add(Imprint $entity, bool $flush = false): void {
		$this->getEntityManager()->persist($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Imprint $entity, bool $flush = false): void {
		$this->getEntityManager()->remove($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function findByName(?string $name = '',
							   ?int    $page = null,
							   ?int    $limit = null,
							   ?string $sort = 'name',
							   string  $order = 'ASC'): array {
		$query = $this->createQueryBuilder('i')
			->where('i.name LIKE :name')
			->setParameter('name', '%'.$name.'%');

		if($page !== null && $limit !== null) {
			$query->setFirstResult($page * $limit)
				->setMaxResults($limit);
		}

		if($sort !== null) {
			$query->orderBy('c.'.$sort, $order);
		}

		return $query->getQuery()
			->getResult();
	}

//    /**
//     * @return Imprint[] Returns an array of Imprint objects
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

//    public function findOneBySomeField($value): ?Imprint
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
