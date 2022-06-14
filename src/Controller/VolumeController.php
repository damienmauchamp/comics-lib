<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Volume;
use App\Repository\IssueRepository;
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
						   IssueRepository     $issueRepo,
						   int                 $id): JsonResponse {

		$force_update = $requestStack->getCurrentRequest()->get('force_update', true);

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
					'url' => "https://comicvine.gamespot.com/publisher/4050-{$volume->getIdc()}/"
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
			$publisherResponse = $this->forward('App\Controller\PublisherController::update', [
				'id' => $id_publisher,
			]);
			$publisher = $publisherRepo->findOneBy(['idc' => $id_publisher]);
		}
		$volume->setPublisher($publisher);

		// saving the volume
		$volumeRepo->add($volume, true);

		// getting the issues from the request
		$issuesResponse = $api->volumeIssues($id);
		if($issuesResponse['error']) {
			return new JsonResponse([
				'status' => 'error',
				'message' => "Couldn't add or update the issues : ".($issuesResponse['message'] ?? ""),
				'response' => $issuesResponse,
			]);
		}
		$issuesData = $issuesResponse['data'];

		// updating the issues
		foreach($issuesData as $issueData) {
			$id_issue = $issueData['id'];

			// checking if the issue exists
			$issue = $issueRepo->findOneBy(['idc' => $id_issue]);
			if(empty($issue)) {
				// if not, we create it
				$issue = new Issue();
				$issue->setDateAdded(new DateTime());
			}

			// checking the updated date
			$updated = new DateTime($issueData['date_last_updated']);
			if($updated < $issue->getDateUpdated()) {
				continue;
			}

			// updating the issue
			$issue->setVolume($volume);
			$issue->setIdc($id_issue);
			$issue->setName($issueData['name']);
//			$issue->setDescription($issueData['deck']);
			$issue->setImage($issueData['image']['original_url']);
			$issue->setDateUpdated(new DateTime());
			$issue->setNumber($issueData['issue_number']);
			$issue->setDateReleased(new DateTime($issueData['store_date']));
//			"https://comicvine.gamespot.com/issue/4000-{$issue->getIdc()}/"

			// saving the issue
			$issueRepo->add($issue, true);
//			$this->forward('App\Controller\IssueController::update', [
//				'id' => $issueData['id'],
//			]);
		}

		return new JsonResponse([
			'status' => 'success',
			'message' => 'The volume has been updated',
			'volume' => [
				'idc' => $volume->getIdc(),
				'name' => $volume->getName(),
				'description' => $volume->getDescription(),
				'image' => $volume->getImage(),
				'number_issues' => $volume->getNumberIssues(),
				'issues_count' => $volume->getIssues()->count(),
				'issues' => $volume->getIssues(),
				'publisher' => [
					'idc' => $volume->getPublisher()->getIdc(),
					'name' => $volume->getPublisher()->getName(),
					'description' => $volume->getPublisher()->getDescription(),
					'image' => $volume->getPublisher()->getImage(),
				],
				'start_year' => $volume->getStartYear(),
				'date_added' => $volume->getDateAdded()->format('Y-m-d H:i:s'),
				'date_updated' => $volume->getDateUpdated()->format('Y-m-d H:i:s'),
				'url' => "https://comicvine.gamespot.com/volume/4050-{$volume->getIdc()}/"
			],
		]);

	}
}