<?php

namespace App\Controller\Search;

use App\Entity\Publisher;
use App\Repository\PublisherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublisherSearchController extends SearchController {
	#[Route('/search/publisher', name: 'app_search_publisher_search')]
	public function search(RequestStack        $requestStack,
						   PublisherRepository $publisherRepo): Response {

		$request = $requestStack->getCurrentRequest();

		$this->handleRequest($request, Publisher::class);

		$query = $request->query->get('q');

		$publishers = $publisherRepo->findByName($query,
			$this->page, $this->limit, $this->sort, $this->order);

		dd($publishers);

		return $this->render('search/publisher_search/index.html.twig', [
			'controller_name' => 'PublisherSearchController',
		]);
	}
}
