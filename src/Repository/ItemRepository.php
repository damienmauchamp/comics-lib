<?php

namespace App\Repository;

use App\Entity\Issue;
use App\Entity\Item;
use App\Entity\ItemIssue;
use App\Entity\Volume;
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

	public function findByVolume(Volume $volume): array {

		$volume_query = $this->getEntityManager()->createQueryBuilder()
			->select('iii.id')
			->from(Issue::class, 'iii')
			->where('iii.volume = :volume');

		$query = $this->createQueryBuilder('i');
		$query
			->select('i')
			->innerJoin(ItemIssue::class, 'ii', 'WITH', 'ii.item = i.id')
			->where($query->expr()->in('ii.issue', $volume_query->getDQL()))
			->setParameter('volume', $volume->getId());

		return $query->getQuery()->getResult();
	}

	public function findNextToReadItems(bool    $started = null,
										bool    $ignored = false,
										?int    $page = null,
										?int    $limit = null,
										string  $sort = 'last_read',
										?string $order = 'DESC'): array {

		$order_by = $sort === 'last_read' ? null : 'release_date';

		// getting next to read items
		$nextToReadItems = $this->findByAttributes(
			null,
			null,
			null,
			null,
			null,
			null,
			$ignored,
			$page,
			$limit,
			$order_by, $order);

		// getting next to read issues of each item
		$items = [];
		foreach($nextToReadItems as $item) {
			$itemIssues = $item->getIssues()->getValues();

//			// setting the read issues
			$item->setIssuesProgress();
			// todo : isComplete

			// checking if all the issues are read
			$allRead = true;
			$isStarted = false;
			/** @var ItemIssue $itemIssue */
			foreach($itemIssues as $itemIssue) {
				$issueRead = $itemIssue->getIssue()->isRead();
				$isStarted = $isStarted || $issueRead;
				$allRead = $allRead && $issueRead;
			}

			if($allRead) {
				continue;
			}

			if($started && !$isStarted) {
				// if we only want started items and this item has no read issues
				continue;
			}

			if($started === false && $isStarted) {
				continue;
			}

			$items[] = $item;
		}
		return $items;

	}

	public function findUpToDateItems(?int    $limit = null,
									  string  $sort = 'last_read',
									  ?string $order = 'DESC'): array {

//		$order_by = $sort === 'last_read' ? null : 'release_date';
		$order_by = 'release_date';

		// getting next to read volumes
		$upToReadItems = $this->findByAttributes(
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			$limit,
			$order_by, $order);

		$items = [];
		foreach($upToReadItems as $item) {
			$itemIssues = $item->getIssues()->getValues();

//			// setting the read issues
			$item->setIssuesProgress();
			// todo : isComplete

			// checking if all the issues are read
			$allRead = true;
			$isStarted = false;
			/** @var ItemIssue $itemIssue */
			foreach($itemIssues as $itemIssue) {
				$issueRead = $itemIssue->getIssue()->isRead();
				$isStarted = $isStarted || $issueRead;
				$allRead = $allRead && $issueRead;
				if(!$allRead) {
					break;
				}
			}

			if(!$allRead) {
				continue;
			}

			$items[] = $item;
		}

		if($sort === 'last_read') {
			usort($items, function (Item $a, Item $b) {

				// todo double-check
				if(!$b->getLastReadIssue() && !$a->getLastReadIssue()) {
					return $b->getReleaseDate() <=> $a->getReleaseDate();
				}
				else if(!$b->getLastReadIssue()) {
					return 1;
				}
				else if(!$a->getLastReadIssue()) {
					return -1;
				}
				return $b->getLastReadIssue()->getDateRead() <=> $a->getLastReadIssue()->getDateRead();
			});
		}

		return $items;
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
