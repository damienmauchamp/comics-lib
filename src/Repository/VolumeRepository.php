<?php

namespace App\Repository;

use App\Entity\Publisher;
use App\Entity\Volume;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Volume>
 *
 * @method Volume|null find($id, $lockMode = null, $lockVersion = null)
 * @method Volume|null findOneBy(array $criteria, array $orderBy = null)
 * @method Volume[]    findAll()
 * @method Volume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolumeRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Volume::class);
	}

	public function add(Volume $entity, bool $flush = false): void {
		$this->getEntityManager()->persist($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Volume $entity, bool $flush = false): void {
		$this->getEntityManager()->remove($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function findByAttributes(?string    $name = '',
									 ?int       $idc = null,
									 ?Publisher $publisher = null,
									 ?DateTime  $year_from = null,
									 ?DateTime  $year_to = null,
									 ?bool      $ignored = null,
									 ?int       $page = null,
									 ?int       $limit = null,
									 ?string    $order_by = 'start_year',
									 ?string    $order = 'ASC'): array {

		$query = $this->createQueryBuilder('v')
			->where('v.name LIKE :name')
			->setParameter('name', '%'.$name.'%');

		if($idc !== null) {
			$query->andWhere('v.idc = :idc')
				->setParameter('idc', $idc);
		}

		if($publisher !== null) {
			$query->andWhere('v.publisher = :publisher')
				->setParameter('publisher', $publisher);
		}

		if($year_from !== null) {
			$query->andWhere('v.release_from >= :year_from')
				->setParameter('year_from', $year_from);
		}

		if($year_to !== null) {
			$query->andWhere('v.release_to <= :year_to')
				->setParameter('year_to', $year_to);
		}

		if($ignored !== null) {
			$query->andWhere('v.date_ignored IS '.($ignored ? 'NOT' : '').' NULL');
		}

		if($page !== null) {
			$query->setFirstResult(($page - 1) * $limit)
				->setMaxResults($limit);
		}

		if($order_by !== null) {
			$query->orderBy('v.'.$order_by, $order);
		}

		return $query->getQuery()->getResult();


	}

//    /**
//     * @return Volume[] Returns an array of Volume objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Volume
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
