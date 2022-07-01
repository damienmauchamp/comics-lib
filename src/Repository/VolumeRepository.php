<?php

namespace App\Repository;

use App\Entity\Issue;
use App\Entity\Publisher;
use App\Entity\Volume;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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
	public function __construct(ManagerRegistry         $registry,
								private IssueRepository $issueRepository) {
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

	public function findNextToReadVolumes(bool    $started = null,
										  bool    $ignored = false,
										  ?int    $limit = null,
										  string  $sort = 'last_read',
										  ?string $order = 'DESC'): array {

		$order_by = $sort === 'last_read' ? null : 'start_year';

		// getting next to read volumes
		$nextToReadVolumes = $this->findByAttributes(
			null,
			null,
			null,
			null,
			null,
			$ignored,
			null,
			$limit,
			$order_by, $order);

		// getting next to read issues of each volume
		$volumes = [];
		foreach($nextToReadVolumes as $volume) {

			// checking if all the volume issues are read
//			$allRead = true;
			$issuesNotRead = $this->issueRepository->findByVolume($volume, false);
			if(count($issuesNotRead) === 0) {
				continue;
			}

			// setting the read issues
			$issuesRead = $this->issueRepository->findByVolume($volume, true, 'date_read', 'DESC');
			$volume->setIssuesRead($issuesRead);

			// setting the next to read issue
//			$issueNextToRead = $issuesNotRead[0];
			// handling multiple issues not read between read ones
			$issueLastRead = $volume->getLastReadIssue();
			$issueNextToRead = $this->issueRepository->findVolumeNextToReadIssue($volume, $issueLastRead);
			$volume->setLastreadissue($issueLastRead);
			$volume->setNextToreadissue($issueNextToRead);

			if($started && !count($issuesRead)) {
				// if we only want started volumes and this volume has no read issues
				continue;
			}

			if($started === false && count($issuesRead)) {
				// if we only want not started volumes and this volume has read issues
				continue;
			}

			// adding the volume to the list
			$volumes[] = $volume;

//			dd($volume, $volume->getLastReadIssue(), $issuesNotRead);
		}

		if($sort === 'last_read') {
			usort($volumes, function (Volume $a, Volume $b) use ($started) {
				if($started) {
					return $b->getLastReadIssue()->getDateRead() <=> $a->getLastReadIssue()->getDateRead();
				}
				else if($started === false) {
					return $b->getDateAdded() <=> $a->getDateAdded();
				}

				if($a->getLastReadIssue() === null && $b->getLastReadIssue() === null) {
					return $b->getDateAdded() <=> $a->getDateAdded();
				}

				if($a->getLastReadIssue() === null) {
					return 1;
				}
				else if($b->getLastReadIssue() === null) {
					return -1;
				}
				return $b->getLastReadIssue()->getDateRead() <=> $a->getLastReadIssue()->getDateRead();
			});
		}

		return $volumes;
	}

	public function findUpToDateVolumes(?int    $limit = null,
										string  $sort = 'last_read',
										?string $order = 'DESC'): array {

		$order_by = $sort === 'last_read' ? null : 'start_year';

		// getting next to read volumes
		$upToReadVolumes = $this->findByAttributes(
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			$limit,
			$order_by, $order);

		$volumes = [];
		foreach($upToReadVolumes as $volume) {

			$issuesNotRead = $this->issueRepository->findByVolume($volume, false);
			if(count($issuesNotRead) > 0) {
				continue;
			}

			// setting the read issues
			$issuesRead = $this->issueRepository->findByVolume($volume, true, 'date_read', 'DESC');
			$volume->setIssuesRead($issuesRead);

			$volumes[] = $volume;
		}

		if($sort === 'last_read') {
			usort($volumes, function (Volume $a, Volume $b) {
				return $b->getLastReadIssue()->getDateRead() <=> $a->getLastReadIssue()->getDateRead();
			});
		}

		return $volumes;
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
