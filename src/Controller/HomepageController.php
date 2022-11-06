<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Repository\VolumeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController {
	#[Route('/', name: 'home')]
	public function index(VolumeRepository $volumeRepository,
						  ItemRepository   $itemRepository): Response {

		/**
		 * VOLUMES
		 */
		// getting next to read issues (volumes with only next to read as attribute, issues: [])
//		$nextToReadVolumesAll = $volumeRepository->findNextToReadVolumes();

		// started section
		$nextToReadVolumesStarted = $volumeRepository->findNextToReadVolumes(true);

		// not started section
		$nextToReadVolumesNotStarted = $volumeRepository->findNextToReadVolumes(false, false);

		// up to date section/link (?)
		$upToDateVolumes = $volumeRepository->findUpToDateVolumes();

		// todo: see ignored link (in twig)
//		dd($nextToReadVolumesStarted, $nextToReadVolumesNotStarted, $upToDateVolumes);

		/**
		 * ITEMS
		 */

		// started section
		$nextToReadItemsStarted = $itemRepository->findNextToReadItems(true,
			false, 1, 10);

		// not started section
		$nextToReadItemsNotStarted = $itemRepository->findNextToReadItems(false,
			false, 1, 10);

		// up to date section/link (?)
		$upToDateItems = $itemRepository->findUpToDateItems();

		return $this->render('homepage/index.html.twig', [
			'volumes' => [
				'nextToReadStarted' => $nextToReadVolumesStarted,
				'nextToReadNotStarted' => $nextToReadVolumesNotStarted,
				'upToDate' => $upToDateVolumes,
			],
			'items' => [
				'nextToReadStarted' => $nextToReadItemsStarted,
				'nextToReadNotStarted' => $nextToReadItemsNotStarted,
				'upToDate' => $upToDateItems,
			],
		]);
	}
}
