<?php

namespace App\Repository;

use App\Entity\Item;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 *
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Item::class);
	}

	public function add(Item $entity, bool $flush = false): void {
		$this->getEntityManager()->persist($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Item $entity, bool $flush = false): void {
		$this->getEntityManager()->remove($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function findByAttributes(?string   $title = '',
									 ?int      $number = null,
									 ?DateTime $release_from = null,
									 ?DateTime $release_to = null,
									 ?string   $isbn = null,
									 ?bool     $special = null,
									 ?bool     $ignored = null,
									 ?int      $page = null,
									 ?int      $limit = null,
									 ?string   $order_by = 'release_date',
									 ?string   $order = 'ASC'): array {

		$query = $this->createQueryBuilder('i')
			->where('i.title LIKE :title')
			->setParameter('title', '%'.$title.'%');

		if($number !== null) {
			$query->andWhere('i.number = :number')
				->setParameter('number', $number);
		}

		if($release_from !== null) {
			$query->andWhere('i.release_from >= :release_from')
				->setParameter('release_from', $release_from);
		}

		if($release_to !== null) {
			$query->andWhere('i.release_to <= :release_to')
				->setParameter('release_to', $release_to);
		}

		if($isbn !== null) {
			$query->andWhere('i.isbn LIKE :isbn')
				->setParameter('isbn', '%'.$isbn.'%');
		}

		if($special !== null) {
			$query->andWhere('i.special = :special')
				->setParameter('special', $special);
		}

		if($ignored !== null) {
			$query->andWhere('i.date_ignored IS '.($ignored ? 'NOT' : '').' NULL');
		}

		if($page !== null && $limit !== null) {
			$query->setFirstResult(($page - 1) * $limit)
				->setMaxResults($limit);
		}

		if($order_by !== null) {
			$query->orderBy('i.'.$order_by, $order);
		}

		return $query->getQuery()->getResult();
	}

//    /**
//     * @return Item[] Returns an array of Item objects
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

//    public function findOneBySomeField($value): ?Item
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
