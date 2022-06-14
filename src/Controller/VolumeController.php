<?php

namespace App\Controller;

use App\Entity\Volume;
use App\Repository\PublisherRepository;
use App\Repository\VolumeRepository;
use App\Service\APIService;
use DateTime;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class VolumeController extends AbstractController {
	#[NoReturn] #[Route('/volume/{id}/update', name: 'app_volume_update',
		methods: ['GET', 'POST'])]
	public function update(APIService          $api,
						   RequestStack        $requestStack,
						   VolumeRepository    $volumeRepo,
						   PublisherRepository $publisherRepo,
						   int                 $id): JsonResponse {

		$force_update = $requestStack->getCurrentRequest()->get('force_update', false);

		// checking if the volume exists
		$volume = $volumeRepo->findOneBy(['idc' => $id]);
		if(empty($volume)) {
			// if not, we create it
			$volume = new Volume();
			$volume->setDateAdded(new DateTime());
		}

		// getting the data from the request
		$response = $api->volume($id);
		if($response['error']) {
			return new JsonResponse([
				'status' => 'error',
				'message' => 'Volume not found or API error : '.($response['message'] ?? ""),
				'response' => $response,
			]);
		}
		$volumeData = $response['data'];

		// checking the updated date
		$updated = new DateTime($volumeData['date_last_updated']);
		if(!$force_update && $updated < $volume->getDateUpdated()) {
			return new JsonResponse([
				'status' => 'ok',
				'message' => 'The volume is already up to date',
				'volume' => [
					'idc' => $volume->getIdc(),
					'name' => $volume->getName(),
					'description' => $volume->getDescription(),
					'image' => $volume->getImage(),
					'number_issues' => $volume->getNumberIssues(),
					'publisher' => [
						'idc' => $volume->getPublisher()->getIdc(),
						'name' => $volume->getPublisher()->getName(),
						'description' => $volume->getPublisher()->getDescription(),
						'image' => $volume->getPublisher()->getImage(),
					],
					'start_year' => $volume->getStartYear(),
					'date_added' => $volume->getDateAdded()->format('Y-m-d H:i:s'),
					'date_updated' => $volume->getDateUpdated()->format('Y-m-d H:i:s'),
					'url' => "https://comicvine.gamespot.com/urban-comics/4050-{$volume->getIdc()}/"
				],
			]);
		}

		// updating the volume
		$volume->setIdc($volumeData['id']);
		$volume->setName($volumeData['name']);
		$volume->setDescription($volumeData['deck']);
		$volume->setImage($volumeData['image']['original_url']);
		$volume->setDateUpdated(new DateTime());
		$volume->setNumberIssues($volumeData['count_of_issues']);
		$volume->setStartYear($volumeData['start_year']);

		// trying to find the publisher
		$id_publisher = $volumeData['publisher']['id'];
		$publisher = $publisherRepo->findOneBy(['idc' => $id_publisher]);

		// if we can't find the publisher or his updated date is older than one month, we create/update it
		if(empty($publisher) || $publisher->getDateUpdated() < ((new DateTime())->sub(new \DateInterval('P1M')))) {
			$response = $this->forward('App\Controller\PublisherController::update', [
				'id' => $id_publisher,
			]);
			$publisher = $publisherRepo->findOneBy(['idc' => $id_publisher]);
		}
		$volume->setPublisher($publisher);

		// saving the volume
		$volumeRepo->add($volume, true);

		// todo : add volume issues
//		dd($volume, $volumeData, $publisher, $response);

		return new JsonResponse([
			'status' => 'success',
			'message' => 'The volume has been updated',
			'volume' => [
				'idc' => $volume->getIdc(),
				'name' => $volume->getName(),
				'description' => $volume->getDescription(),
				'image' => $volume->getImage(),
				'number_issues' => $volume->getNumberIssues(),
				'publisher' => [
					'idc' => $volume->getPublisher()->getIdc(),
					'name' => $volume->getPublisher()->getName(),
					'description' => $volume->getPublisher()->getDescription(),
					'image' => $volume->getPublisher()->getImage(),
				],
				'start_year' => $volume->getStartYear(),
				'date_added' => $volume->getDateAdded()->format('Y-m-d H:i:s'),
				'date_updated' => $volume->getDateUpdated()->format('Y-m-d H:i:s'),
				'url' => "https://comicvine.gamespot.com/urban-comics/4050-{$volume->getIdc()}/"
			],
		]);

	}
}
