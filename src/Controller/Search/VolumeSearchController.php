<?php

namespace App\Controller\Search;

use App\Entity\Volume;
use App\Repository\PublisherRepository;
use App\Repository\VolumeRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VolumeSearchController extends SearchController {
	/**
	 * @throws Exception
	 */
	#[Route('/search/volume', name: 'app_search_volume_search')]
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
			$this->page, $this->limit, $this->sort, $this->order);

		dd($volumes, [
			'limit' => $this->limit,
			'page' => $this->page,
			'results' => count($volumes),
		]);


		// todo : idc
		// todo : publisher
		// todo : name
		// todo : start_year (between)
		// todo : ignored (default: false)

		return $this->render('search/volume_search/index.html.twig', [
			'controller_name' => 'VolumeSearchController',
		]);
	}
}
