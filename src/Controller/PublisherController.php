<?php

namespace App\Controller;

use App\Entity\Publisher;
use App\Repository\PublisherRepository;
use App\Service\APIService;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublisherController extends AbstractController {

	#[NoReturn] #[Route('/publisher/{id}/update', name: 'app_publisher_update',
		methods: ['GET', 'POST'])]
	/**
	 * @throws Exception
	 * @todo remove GET
	 * @todo make the route POST "/publisher/{id}"
	 */
	public function update(APIService          $api,
						   RequestStack        $requestStack,
						   PublisherRepository $publisherRepo,
						   int                 $id): Response {

		// checking if the publisher exists
		$publisher = $publisherRepo->findOneBy(['idc' => $id]);
		if(empty($publisher)) {
			$publisher = new Publisher();
			$publisher->setDateAdded(new \DateTime());
		}

		// if not, we create it
		$response = $api->publisher($id);
		if($response['error']) {
			return new JsonResponse([
				'status' => 'error',
				'message' => 'Publisher not found or API error : '.($response['message'] ?? ""),
				'response' => $response,
			]);
		}
		$publisherData = $response['data'];

		// checking the updated date
		$updated = new \DateTime($publisherData['date_last_updated']);
		if($updated < $publisher->getDateUpdated()) {
			return new JsonResponse([
				'status' => 'ok',
				'message' => 'The publisher is already up to date',
				'publisher' => [
					'idc' => $publisher->getIdc(),
					'name' => $publisher->getName(),
					'description' => $publisher->getDescription(),
					'image' => $publisher->getImage(),
					'date_added' => $publisher->getDateAdded()->format('Y-m-d H:i:s'),
					'date_updated' => $publisher->getDateUpdated()->format('Y-m-d H:i:s'),
					'date_last_updated' => $publisherData['date_last_updated'],
					'url' => "https://comicvine.gamespot.com/urban-comics/4010-{$publisher->getIdc()}/",
				],
			]);
		}

		// updating the publisher
		$publisher->setIdc($publisherData['id']);
		$publisher->setName($publisherData['name']);
		$publisher->setDescription($publisherData['deck']);
		$publisher->setImage($publisherData['image']['original_url']);
		$publisher->setDateUpdated(new \DateTime());

		// saving the publisher
		$publisherRepo->add($publisher, true);

		return new JsonResponse([
			'status' => 'success',
			'message' => 'The publisher has been updated',
			'publisher' => [
				'idc' => $publisher->getIdc(),
				'name' => $publisher->getName(),
				'description' => $publisher->getDescription(),
				'image' => $publisher->getImage(),
				'date_added' => $publisher->getDateAdded()->format('Y-m-d H:i:s'),
				'date_updated' => $publisher->getDateUpdated()->format('Y-m-d H:i:s'),
				'url' => "https://comicvine.gamespot.com/urban-comics/4010-{$publisher->getIdc()}/"
			],
		]);
	}

}
