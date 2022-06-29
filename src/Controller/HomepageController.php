<?php

namespace App\Controller;

use App\Repository\VolumeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController {
	#[Route('/homepage', name: 'app_homepage')]
	public function index(VolumeRepository $volumeRepository): Response {

		// VOLUMES
		// getting next to read issues (volumes with only next to read as attribute, issues: [])
//		$nextToReadVolumesAll = $volumeRepository->findNextToReadVolumes();

		// started section
		$nextToReadVolumesStarted = $volumeRepository->findNextToReadVolumes(true);

		// not started section
		$nextToReadVolumesNotStarted = $volumeRepository->findNextToReadVolumes(false, false);

		// up to date section/link (?)
		$upToDateVolumes = $volumeRepository->findUpToDateVolumes();

		// todo: see ignored link (in twig)

		dd($nextToReadVolumesStarted, $nextToReadVolumesNotStarted, $upToDateVolumes);

		// todo: ITEMS

		return $this->render('homepage/index.html.twig', [
			'controller_name' => 'HomepageController',
		]);
	}
}
