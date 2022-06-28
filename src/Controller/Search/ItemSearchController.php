<?php

namespace App\Controller\Search;

use App\Entity\Item;
use App\Repository\ItemRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemSearchController extends SearchController {
	/**
	 * @throws Exception
	 */
	#[Route('/search/item', name: 'app_search_item_search')]
	public function search(RequestStack   $requestStack,
						   ItemRepository $itemRepository): Response {

		$request = $requestStack->getCurrentRequest();

		$this->handleRequest($request, Item::class);

		$query = $request->query->get('q'); // title
		$number = $request->query->get('number');
		$release_from = $request->query->get('release_from');
		$release_to = $request->query->get('release_to');
		$isbn = $request->query->get('isbn');
		$special = $request->query->get('special');
		$ignored = $request->query->get('ignored');

		// handling date range
		// todo : handle exception if date is not valid
		$release_from = $release_from ? new DateTime($release_from) : null;
		$release_to = $release_to ? new DateTime($release_to) : null;

		$items = $itemRepository->findByAttributes(
			$query,
			$number,
			$release_from,
			$release_to,
			$isbn,
			$special,
			$ignored,
			$this->page, $this->limit, $this->sort, $this->order);

		dd($items, [
			'limit' => $this->limit,
			'page' => $this->page,
			'results' => count($items),
		]);

		return $this->render('search/item_search/index.html.twig', [
			'controller_name' => 'ItemSearchController',
		]);
	}
}
