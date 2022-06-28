<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Volume;
use App\Repository\IssueRepository;
use App\Repository\PublisherRepository;
use App\Repository\VolumeRepository;
use App\Service\APIService;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class VolumeController extends AbstractController {


	/**
	 * ADD
	 * @todo remove GET
	 */
	#[NoReturn] #[Route('/volume/{id<\d+>}/add', name: 'app_volume_add',
		methods: ['GET', 'POST'])]
	public function add(int     $id,
						array   $params = [],
						array   $issues = [],
						int     $id_force = null,
						int     $id_start = null,
						?string $interval = null): JsonResponse {

		$response = $this->forward('App\Controller\VolumeController::update', [
			'id' => $id,
			'params' => $params,
			'issues' => $issues,
			'id_force' => $id_force,
			'id_start' => $id_start,
			'interval' => $interval,
		]);
		return new JsonResponse(json_decode($response->getContent(), true));
	}


	/**
	 * ADD + UPDATE
	 * @todo remove GET
	 */
	#[NoReturn] #[Route('/volume/{id<\d+>}/update', name: 'app_volume_update',
		methods: ['GET', 'POST'])]
	public function update(ManagerRegistry     $doctrine,
						   APIService          $api,
						   RequestStack        $requestStack,
						   VolumeRepository    $volumeRepo,
						   PublisherRepository $publisherRepo,
						   IssueRepository     $issueRepo,
						   int                 $id,
						   array               $params = [],
						   array               $issues = [],
						   int                 $id_force = null,
						   int                 $id_start = null,
						   ?string             $interval = null): JsonResponse {

		$force_update = $requestStack->getCurrentRequest()->get('force_update', true);

		// checking if the volume exists
		$volume = $volumeRepo->findOneBy(['idc' => $id]);
		if(empty($volume)) {
			// if not, we return an error
			return new JsonResponse([
				'status' => 'error',
				'message' => 'Volume not found',
			]);
//			// if not, we create it
//			$volume = new Volume();
//			$volume->setDateAdded(new DateTime());
		}
		else if($id_force && $id_force === $volume->getId() || $id_start && $id_start <= $volume->getId()) {
			$force_update = true;
		}

//		if()  PT1H30M

		if($id_force && $id_force != $volume->getId() ||
			$id_start && $volume->getId() && $id_start > $volume->getId() ||
//			$interval && $volume->getDateUpdated() && $volume->getDateUpdated()->diff(new DateTime())->format('%R%a') < $interval) {
			$interval && $volume->getDateUpdated() && $volume->getDateUpdated() > (new DateTime())->sub(new DateInterval($interval))) {
			return new JsonResponse([
				'status' => 'ok',
				'message' => "No need to update",
				'skipped' => true,
				'response' => [],
			]);
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
		$volume->setDateIgnored($params['date_ignored'] ?? null);

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
			$date_read = null;

			$key = array_search($id_issue, array_column($issues, 'id_comicvine'));
			if(isset($issues[$key]) && $issues[$key]['read'] !== null) {
				$date_read = new DateTime($issues[$key]['read']);
			}

			// checking if the issue exists
			$issue = $issueRepo->findOneBy(['idc' => $id_issue]);
			if(empty($issue)) {
				// if not, we create it
				$issue = new Issue();
				$issue->setDateAdded(new DateTime());
			}

			// checking the updated date
			$updated = new DateTime($issueData['date_last_updated']);
			if($updated < $issue->getDateUpdated() && !$force_update) {
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
			$issue->setDateRead($date_read);
//			"https://comicvine.gamespot.com/issue/4000-{$issue->getIdc()}/"

			// saving the issue
			$issueRepo->add($issue);
//			$this->forward('App\Controller\IssueController::update', [
//				'id' => $issueData['id'],
//			]);
		}
		$doctrine->getManager()->flush();

//		dd($issuesData, $volume);

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
//				'issues' => $volume->getIssues(),
//				isRead
//				isIgnored
				'issues' => array_map(function ($issue) {
//					$issue->setIsRead($issue->getDateRead() !== null);
					return [
						'idc' => $issue->getIdc(),
						'name' => $issue->getName(),
//						'description' => $issue->getDescription(),
						'image' => $issue->getImage(),
						'date_released' => $issue->getDateReleased()->format('Y-m-d H:i:s'),
						'date_read' => $issue->getDateRead()?->format('Y-m-d H:i:s'),
						'is_read' => $issue->isRead(),
						'is_ignored' => $issue->isIgnored(),
					];
				}, $volume->getIssues()->toArray()),
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

//	public function add()
}
