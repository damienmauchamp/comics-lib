<?php

namespace App\Controller\Search;

use App\Entity\Imprint;
use App\Repository\ImprintRepository;
use App\Repository\ItemCollectionRepository;
use App\Repository\TypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemCollectionSearchController extends SearchController {
	#[Route('/search/collection', name: 'app_search_collection_search')]
	public function search(RequestStack             $requestStack,
						   ItemCollectionRepository $collectionRepository,
						   TypeRepository           $typeRepository,
						   ImprintRepository        $imprintRepository): Response {

		$request = $requestStack->getCurrentRequest();

		$this->handleRequest($request, Imprint::class);

		$query = $request->query->get('q');

		$id_type = $request->query->get('type');
		$type = $id_type ? $typeRepository->find($id_type) : null;

		$official = $request->query->get('official');

		$id_imprint = $request->query->get('imprint');
		$imprint = $id_imprint ? $imprintRepository->find($id_imprint) : null;

		$collections = $collectionRepository->findByAttributes(
			$query,
			$type,
			$official,
			$imprint,
			$this->page, $this->limit, $this->sort, $this->order);

		dd($collections, [
			'limit' => $this->limit,
			'page' => $this->page,
			'results' => count($collections),
		]);

		return $this->render('search/item_collection_search/index.html.twig', [
			'controller_name' => 'ItemCollectionSearchController',
		]);
	}
}
