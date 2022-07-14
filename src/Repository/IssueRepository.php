<?php

namespace App\Repository;

use App\Entity\Issue;
use App\Entity\Item;
use App\Entity\Volume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Issue>
 *
 * @method Issue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Issue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Issue[]    findAll()
 * @method Issue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Issue::class);
	}

	public function add(Issue $entity, bool $flush = false): void {
		$this->getEntityManager()->persist($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function update(Issue $entity, bool $flush = false): void {
		$this->add($entity, $flush);
	}

	public function remove(Issue $entity, bool $flush = false): void {
		$this->getEntityManager()->remove($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	/**
	 */
	public function findVolumeNextToReadIssue(Volume $volume,
											  ?Issue $lastReadIssue = null): ?Issue {

		if($lastReadIssue === null || $lastReadIssue->getDateRead() === null) {
			$lastReadIssue = $volume->getLastReadIssue();
		}

		if($lastReadIssue === null) {
			return $this->findByVolume($volume, false)[0] ?? null;
		}

		$query = $this->createQueryBuilder('i')
			->where('i.volume = :volume')
//			->andWhere('i.number > :number')
//			->andWhere('cast(i.number as date_released) > :number')
			->andWhere('i.date_released > :date_released')
			->andWhere('i.date_read is null')
			->setParameter('volume', $volume)
//			->setParameter('number', (int)$lastReadIssue->getNumber())
			->setParameter('date_released', $lastReadIssue->getDateReleased())
//			->orderBy('i.number', 'ASC')
			->orderBy('i.date_released', 'ASC')
			->setMaxResults(1);

		$result = $query->getQuery();
		try {
			return $result->getSingleResult();
		} catch(NoResultException $e) {
			return $this->findByVolume($volume, false)[0];
		} catch(NonUniqueResultException $e) {
			return $result->getResult()[0];
		}
	}

	public function findItemNextToReadIssue(Item   $item,
											?Issue $lastReadIssue = null): ?Issue {
		// todo
		return null;
	}

	public function findByVolume(Volume $volume,
								 bool   $read = false,
								 string $sort = 'number', string $order = 'ASC'): array {
		$query = $this->createQueryBuilder('i')
			->where('i.volume = :volume')
			->setParameter('volume', $volume)
			->andWhere('i.date_read IS '.($read ? 'NOT' : '').' NULL')
			->orderBy("i.{$sort}", $order);

		return $query->getQuery()
			->getResult();
	}

//    /**
//     * @return Issue[] Returns an array of Issue objects
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

//    public function findOneBySomeField($value): ?Issue
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
