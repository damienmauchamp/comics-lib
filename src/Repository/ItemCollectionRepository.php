<?php

namespace App\Repository;

use App\Entity\Imprint;
use App\Entity\ItemCollection;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemCollection>
 *
 * @method ItemCollection|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemCollection|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemCollection[]    findAll()
 * @method ItemCollection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemCollectionRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ItemCollection::class);
	}

	public function add(ItemCollection $entity, bool $flush = false): void {
		$this->getEntityManager()->persist($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(ItemCollection $entity, bool $flush = false): void {
		$this->getEntityManager()->remove($entity);

		if($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function findByAttributes(?string  $name = '',
									 ?Type    $type = null,
									 ?bool    $official = null,
									 ?Imprint $imprint = null,
									 ?int     $page = null,
									 ?int     $limit = null,
									 ?string  $order_by = 'name',
									 string   $order = 'ASC'): array {
		$query = $this->createQueryBuilder('c')
			->where('c.name LIKE :name')
			->setParameter('name', '%'.$name.'%');


		if($type) {
			$query->andWhere('c.type = :type')
				->setParameter('type', $type);
		}

		if($official !== null) {
			$query->andWhere('c.official = :official')
				->setParameter('official', $official);
		}

		if($imprint) {
			$query->andWhere('c.imprint = :imprint')
				->setParameter('imprint', $imprint);
		}

		if($page !== null && $limit !== null) {
			$query->setFirstResult(($page - 1) * $limit)
				->setMaxResults($limit);
		}

		if($order_by !== null) {
			$query->orderBy('c.'.$order_by, $order);
		}

		return $query->getQuery()
			->getResult();
	}

	public function findByName(?string $name = ''): array {
		return $this->createQueryBuilder('c')
			->where('c.name LIKE :name')
			->setParameter('name', '%'.$name.'%')
			->getQuery()
			->getResult();
	}

//    /**
//     * @return Collection[] Returns an array of Collection objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Collection
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
