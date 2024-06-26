<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\ItemIssue;
use App\Entity\Volume;
use App\Repository\IssueRepository;
use App\Repository\ItemCollectionRepository;
use App\Repository\ItemRepository;
use App\Repository\PublisherRepository;
use App\Repository\VolumeRepository;
use App\Service\APIService;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class VolumeController extends AbstractController {


	/**************************************************************************************************************/
	/***                                               Navigation                                               ***/
	/**************************************************************************************************************/

	/**
	 * Displaying all comics
	 * @param VolumeRepository $volumeRepository
	 * @return Response
	 * @todo Do not load up-to-date now, do it via AJAX
	 */
	#[Route('/comics', name: 'comics')]
	public function list(VolumeRepository $volumeRepository): Response {

		// started comics/volumes
		$nextToReadVolumesStarted = $volumeRepository->findNextToReadVolumes(true);

		// not started comics/volumes
		$nextToReadVolumesNotStarted = $volumeRepository->findNextToReadVolumes(false, false);

		// up-to-date comics/volumes
		$upToDateVolumes = $volumeRepository->findUpToDateVolumes();

		return $this->render('_pages/comics.html.twig', [
//			'volumes' => [
			'nextToReadStarted' => $nextToReadVolumesStarted,
			'nextToReadNotStarted' => $nextToReadVolumesNotStarted,
			'upToDate' => $upToDateVolumes,
//			],
		]);
	}

	/**************************************************************************************************************/
	/***                                                 Actions                                                ***/
	/**************************************************************************************************************/

	/**
	 * Displaying a volume
	 * @param VolumeRepository $volumeRepo
	 * @param IssueRepository $issueRepo
	 * @param ItemRepository $itemRepo
	 * @param int $id
	 * @return Response
	 */
	#[Route('/volume/{id<\d+>}', name: 'app_volume', methods: ['GET'])]
	public function index(VolumeRepository $volumeRepo,
						  IssueRepository  $issueRepo,
						  ItemRepository   $itemRepo,
						  int              $id): Response {

		// page volume
		$volume = $volumeRepo->find($id);
		if(empty($volume)) {
			// if not, we return an error
			return new JsonResponse([
				'status' => 'error',
				'message' => 'Volume not found',
			]);
		}

		// getting publisher
		$publisher = $volume->getPublisher();

		// getting issues list
		$issues = $volume->getIssues()->getValues();

		// getting items list where the volume appears
		$items = $itemRepo->findByVolume($volume);

		$volume->setIssuesProgress($issueRepo);

//		dd($volume);

//		// links between issues and items
//		$itemsIssues = [];
//		foreach($issues as $issue) {
//			$itemsIssues = array_merge($itemsIssues, $issue->getItems()->getValues());
//		}

//		dd($volume, $publisher, $issues, $items);
		return $this->render('volume/index.html.twig', [
			'volume' => $volume,
			'publisher' => $publisher,
			'issues' => $issues,
			'items' => $items,
		]);
	}

	/**
	 * Adding a volume to the library
	 * @param int $idc
	 * @param array $params
	 * @param array $issues
	 * @param int|null $id_force
	 * @param int|null $id_start
	 * @param string|null $interval
	 * @return JsonResponse
	 */
	#[Route('/volume/{idc<\d+>}/add', name: 'app_volume_add', methods: ['POST'])]
	public function add(int     $idc,
						array   $params = [],
						array   $issues = [],
						int     $id_force = null,
						int     $id_start = null,
						?string $interval = null): JsonResponse {

		/** @var JsonResponse $response */
		$response = $this->forward('App\Controller\VolumeController::update', [
			'idc' => $idc,
			'params' => $params,
			'issues' => $issues,
			'id_force' => $id_force,
			'id_start' => $id_start,
			'interval' => $interval,
			'add' => true,
		]);
		$json = json_decode($response->getContent(), true);

		if($json['status'] == 'success') {
			$json['message'] = 'The volume has been added';
		}
		else if($json['status'] == 'ok') {
			$json['message'] = 'The volume is already in your library';
		}
		return new JsonResponse($json);
	}


	/**
	 * Adding/updating a volume to the library
	 * @param ManagerRegistry $doctrine
	 * @param APIService $api
	 * @param RequestStack $requestStack
	 * @param VolumeRepository $volumeRepo
	 * @param PublisherRepository $publisherRepo
	 * @param IssueRepository $issueRepo
	 * @param int $idc
	 * @param array $params
	 * @param array $issues
	 * @param int|null $id_force
	 * @param int|null $id_start
	 * @param string|null $interval
	 * @param bool $add
	 * @return JsonResponse
	 * @throws \Exception
	 */
	#[NoReturn] #[Route('/volume/{idc<\d+>}/update', name: 'app_volume_update', methods: ['POST'])]
	public function update(ManagerRegistry     $doctrine,
						   APIService          $api,
						   RequestStack        $requestStack,
						   VolumeRepository    $volumeRepo,
						   PublisherRepository $publisherRepo,
						   IssueRepository     $issueRepo,
						   int                 $idc,
						   array               $params = [],
						   array               $issues = [],
						   int                 $id_force = null,
						   int                 $id_start = null,
						   ?string             $interval = null,
						   bool                $add = false): JsonResponse {

		$force_update = $requestStack->getCurrentRequest()->get('force_update', true);
		$render = $requestStack->getCurrentRequest()->get('render', true);

		// checking if the volume exists
		$volume = $volumeRepo->findOneBy(['idc' => $idc]);
		if(empty($volume) && $add) {
			$volume = new Volume();
			$volume->setDateAdded(new DateTime());
		}
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
		$response = $api->volume($idc);
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
					'url' => "https://comicvine.gamespot.com/publisher/4050-{$volume->getIdc()}/",
					'html' => $this->renderVolume($volume, $render),
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
		$issuesResponse = $api->volumeIssues($idc);
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
				'id' => $volume->getId(),
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
				'url' => "https://comicvine.gamespot.com/volume/4050-{$volume->getIdc()}/",
				'html' => $this->renderVolume($volume, $render),
			],
		]);

	}


	/**
	 * Read an issue of a volume
	 * @param ManagerRegistry $doctrine
	 * @param VolumeRepository $volumeRepo
	 * @param IssueRepository $issueRepo
	 * @param int $id
	 * @param string $n
	 * @param bool $read
	 * @return JsonResponse
	 */
	#[Route('/volume/{id<\d+>}/issue/{n}/read', name: 'app_volume_read_issue_by_number', methods: ['POST'])]
	public function readIssue(ManagerRegistry  $doctrine,
							  VolumeRepository $volumeRepo,
							  IssueRepository  $issueRepo,
							  int              $id,
							  string           $n,
							  bool             $read = true
	): JsonResponse {

		// getting the volume from the request
		$volume = $volumeRepo->findOneBy(['id' => $id]);
		if(!$volume) {
			return new JsonResponse([
				'status' => 'error',
				'message' => "Couldn't find the volume",
			]);
		}

		// checking if the issue exists
		$issue = $issueRepo->findOneBy(['volume' => $volume, 'number' => $n]);

		// updating the issue
		/** @var JsonResponse $response */
		$response = $this->forward('App\Controller\IssueController::read', [
			'id' => $issue->getId(),
			'volume' => $volume,
			'read' => $read,
			'forward' => 'volume'
		]);
		return $response;
	}


	/**
	 * Unread an issue of a volume
	 * @param ManagerRegistry $doctrine
	 * @param VolumeRepository $volumeRepo
	 * @param IssueRepository $issueRepo
	 * @param int $id
	 * @param string $n
	 * @return JsonResponse
	 */
	#[Route('/volume/{id<\d+>}/issue/{n}/unread', name: 'app_volume_read_issue_by_number', methods: ['POST'])]
	public function unreadIssue(ManagerRegistry  $doctrine,
								VolumeRepository $volumeRepo,
								IssueRepository  $issueRepo,
								int              $id,
								string           $n
	): JsonResponse {
		return $this->readIssue($doctrine, $volumeRepo, $issueRepo, $id, $n, false);
	}

	/**
	 * Ignore an issue of a volume
	 * @param ManagerRegistry $doctrine
	 * @param VolumeRepository $volumeRepo
	 * @param IssueRepository $issueRepo
	 * @param int $id
	 * @param string $n
	 * @param bool $ignore
	 * @return JsonResponse
	 */
	#[Route('/volume/{id<\d+>}/issue/{n}/ignore', name: 'app_volume_ignore_issue_by_number', methods: ['POST'])]
	public function ignoreIssue(ManagerRegistry  $doctrine,
								VolumeRepository $volumeRepo,
								IssueRepository  $issueRepo,
								int              $id,
								string           $n,
								bool             $ignore = true
	): JsonResponse {

		// getting the volume from the request
		$volume = $volumeRepo->findOneBy(['id' => $id]);
		if(!$volume) {
			return new JsonResponse([
				'status' => 'error',
				'message' => "Couldn't find the volume",
			]);
		}

		// checking if the issue exists
		$issue = $issueRepo->findOneBy(['volume' => $volume, 'number' => $n]);

		// updating the issue
		/** @var JsonResponse $response */
		$response = $this->forward('App\Controller\IssueController::ignore', [
			'id' => $issue->getId(),
			'volume' => $volume,
			'ignore' => $ignore,
		]);
		return $response;
	}

	/**
	 * Stop ignoring an issue of a volume
	 * @param ManagerRegistry $doctrine
	 * @param VolumeRepository $volumeRepo
	 * @param IssueRepository $issueRepo
	 * @param int $id
	 * @param string $n
	 * @return JsonResponse
	 */
	#[Route('/volume/{id<\d+>}/issue/{n}/unignore', name: 'app_volume_ignore_issue_by_number', methods: ['POST'])]
	public function unignoreIssue(ManagerRegistry  $doctrine,
								  VolumeRepository $volumeRepo,
								  IssueRepository  $issueRepo,
								  int              $id,
								  string           $n
	): JsonResponse {
		return $this->ignoreIssue($doctrine, $volumeRepo, $issueRepo, $id, $n, false);
	}

	/**************************************************************************************************************/
	/***                                                Methods                                                 ***/
	/**************************************************************************************************************/

	/**
	 * Rendering a volume
	 * @param Volume $volume
	 * @param string $render
	 * @param string $display
	 * @return string
	 */
	private function renderVolume(Volume $volume,
								  string $render = '',
								  string $display = ''): string {
		if($render === 'home') {
			return $this->renderView('volume/volume.html.twig', [
				'volume' => $volume,
				'display' => $display,
			]);
		}
		return '';
	}
}
