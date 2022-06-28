<?php

namespace App\Controller\Search;

use App\Entity\Imprint;
use App\Repository\ImprintRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImprintSearchController extends SearchController {
	#[Route('/search/imprint', name: 'app_search_imprint_search')]
	public function search(RequestStack      $requestStack,
						   ImprintRepository $imprintRepository): Response {

		$request = $requestStack->getCurrentRequest();
		$query = $request->query->get('q');

		$this->handleRequest($request, Imprint::class);

		$imprints = $imprintRepository->findByName($query,
			$this->page, $this->limit, $this->sort, $this->order);

		dd($imprints, [
			'limit' => $this->limit,
			'page' => $this->page,
			'results' => count($imprints),
		]);

		return $this->render('search/imprint_search/index.html.twig', [
			'controller_name' => 'ImprintSearchController',
		]);
	}
}
