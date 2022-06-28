<?php

namespace App\Controller\Search;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController {

	private ManagerRegistry $registry;

	protected int $page = 1;
	protected ?int $limit = null;
	protected int $limit_max = 100;
	protected int $limit_default = 10;
	protected ?string $sort = null;
	protected string $order = 'asc';

	public function __construct(ManagerRegistry $registry) {
		$this->registry = $registry;
	}

	protected function handleRequest(Request $request, string $entity = null): void {
		$this->setPage($request->query->get('page'));
		$this->setLimit($request->query->get('limit'));
		$this->setSort($request->query->get('order'), $entity);
		$this->setOrder($request->query->get('direction'));
	}

	protected function setPage(?int $page): void {
		$this->page = $page ?: 1;
	}

	protected function setLimit(?int $limit, bool $default = true): void {

		if($limit && $limit > $this->limit_max) {
			$limit = $this->limit_max;
		}

		if(!$limit && $default) {
			$this->limit = $this->limit_default;
		}
		else {
			$this->limit = $limit;
		}
	}

	protected function setSort(?string $sort, string $class): void {
		$exists = $this->registry->getManager()
			->getClassMetadata($class)
			->hasField($sort);
		if($exists) {
			$this->sort = $sort;
		}
	}

	protected function setOrder(?string $order): void {

		$this->order = $order && !in_array($order, ['asc', 'desc']) ? $order : 'asc';
	}

//	#[Route('/search', name: 'app_search_search')]
//	public function index(): Response {
//		return $this->render('search/search/index.html.twig', [
//			'controller_name' => 'SearchController',
//		]);
//	}
}
