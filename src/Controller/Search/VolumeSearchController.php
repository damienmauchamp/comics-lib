<?php

namespace App\Controller\Search;

use App\Entity\Volume;
use App\Repository\PublisherRepository;
use App\Repository\VolumeRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VolumeSearchController extends SearchController {
	/**
	 * Searching for volumes in library
	 * @throws Exception
	 */
	#[Route('/search/library/volume', name: 'app_search_library_volume')]
	public function search(RequestStack        $requestStack,
						   VolumeRepository    $volumeRepository,
						   PublisherRepository $publisherRepository): Response {

		$request = $requestStack->getCurrentRequest();

		$this->handleRequest($request, Volume::class);

		$name = $request->query->get('q'); // title
		$idc = $request->query->get('idc');
		$id_publisher = $request->query->get('id_publisher');
		$publisher = $id_publisher ? $publisherRepository->find($id_publisher) : null;
		$year_from = $request->query->get('year_from');
		$year_to = $request->query->get('year_to');
		$ignored = $request->query->get('ignored');

		// handling date range
		// todo : handle exception if date is not valid
		$year_from = $year_from ? new DateTime($year_from) : null;
		$year_to = $year_to ? new DateTime($year_to) : null;

		$volumes = $volumeRepository->findByAttributes(
			$name,
			$idc,
			$publisher,
			$year_from,
			$year_to,
			$ignored,
			$this->page, $this->limit, $this->sort, $this->order, true);

		$results = [];
		foreach($volumes as $volume) {
			$results[] = $this->renderVolumeArray($volume);
		}

//		dd($volumes, [
//			'limit' => $this->limit,
//			'page' => $this->page,
//			'results' => count($volumes),
//			'$results' => $results,
//		]);

		return new JsonResponse($results);
	}

	private function renderVolumeArray(?Volume $volume,
									   array   $result = [],
									   string  $img = 'scale_small'): array {

		$image = $volume ? $volume->getImage() : $result['image'][$img] ??
			$result['image']['thumb_url'] ??
			$result['image']['original_url'];

		$publisher = $result['publisher'] ?? null;
		$data = [
			'id' => $volume?->getId(),
			'idc' => $volume ? $volume->getIdc() : ($result['idc'] ?? $result['id']),
			'name' => $volume ? $volume->getName() : $result['name'],
			'full_name' => $volume ? $volume->getFullName() : $result['full_name'],
			'year' => $volume ? $volume->getStartYear() : $result['start_year'],
			'image' => $image,
			'publisher' => $volume ? $volume->getPublisher() : $publisher,
			'publisher_name' => $volume ? $volume?->getPublisher()?->getName() : ($publisher ? $publisher['name'] : null),
			'number_issues' => $result && ($result['count_of_issues'] ?? false) ? $result['count_of_issues'] : ($volume?->getNumberIssues()),
			'type' => 'volume',
			'html' => '',
			'added' => $volume !== null,
//				'volume' => $volume,
		];

		$data['html'] = $this->render('search/search_result.html.twig', [
			'data' => $data,
		])->getContent();

		return $data;
	}

	#[Route('/search/api/volume', name: 'app_search_api_volume')]
	public function apiSearch(RequestStack        $requestStack,
							  VolumeRepository    $volumeRepository,
							  PublisherRepository $publisherRepository): Response {

		$request = $requestStack->getCurrentRequest();

		$this->handleRequest($request, Volume::class);

		$name = $request->query->get('q'); // title
		$page = $request->query->get('page', 1); // page

		$response = $this->forward('App\Controller\ApiController::searchVolume', [
			'name' => $name,
			'page' => $page,
		])->getContent();
		$json = json_decode($response, true);

		//
		$results = [];
		foreach($json['data'] as $result) {
			$idc = $result['id'];
			$publisher = $result['publisher'];
			$full_name = $result['name'].($result['start_year'] ? " ({$result['start_year']})" : '');

			$volume = $volumeRepository->findOneBy(['idc' => $idc]);
			//
			$result['full_name'] = $full_name;
			$data = $this->renderVolumeArray($volume, $result);

//			$data = [
//				'id' => $volume?->getId(),
//				'idc' => $volume ? $volume->getIdc() : $idc,
//				'name' => $volume ? $volume->getName() : $result['name'],
//				'full_name' => $volume ? $volume->getFullName() : $full_name,
//				'year' => $volume ? $volume->getStartYear() : $result['start_year'],
//				'image' => $volume ? $volume->getImage() : $result['image']['original_url'],
//				'publisher' => $volume ? $volume->getPublisher() : $publisher,
//				'publisher_name' => $volume ? $volume?->getPublisher()?->getName() : ($publisher ? $publisher['name'] : null),
//				'number_issues' => $result['count_of_issues'],
//				'type' => 'volume',
//				'html' => '',
//				'added' => $volume !== null,
////				'volume' => $volume,
//			];
//
//			$data['html'] = $this->render('search/search_result.html.twig', [
//				'data' => $data,
//			])->getContent();

			$results[] = $data;
		}

		return new JsonResponse($results);
	}
}
