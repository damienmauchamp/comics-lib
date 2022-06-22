<?php

namespace App\Controller\Import;

use App\Controller\VolumeController;
use DateTime;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComicsLibraryController extends AbstractController {

	private PDO $pdo;

	/**
	 * Import from Comics Library
	 * @link https://github.com/damienmauchamp/comics-library
	 * @param ManagerRegistry $doctrine
	 * @param RequestStack $requestStack
	 * @return JsonResponse
	 */
	#[Route('/import/comics-library', name: 'app_import_comics_library')]
	public function index(ManagerRegistry $doctrine,
						  RequestStack    $requestStack): JsonResponse {

		set_time_limit(0);

		try {
			$this->setPDO();

			$import = $requestStack->getCurrentRequest()->get('import', 'all');
			$import = 'items';
//			dd($import);

			$id_volume = $requestStack->getCurrentRequest()->get('id_volume', null);
			$id_volume_start = $requestStack->getCurrentRequest()->get('id_volume_start', null);

			$results = [
				'volumes' => [],
				'items' => [],
			];

			if(in_array($import, ['all', 'volumes'])) {
				$results['volumes'] = $this->importVolumes($id_volume, $id_volume_start);
			}

			if(in_array($import, ['all', 'items'])) {
				$results['items'] = $this->importItems();
			}

		} catch(Exception $e) {
			return new JsonResponse([
				'status' => 'error',
				'message' => $e->getMessage(),
			]);
		}

		return new JsonResponse([
			'status' => 'success',
			'message' => 'Library imported',
			'info' => [
				'volumes' => count($results['volumes']),
				'items' => count($results['items']),
			],
			'data' => $id_volume ? $results : [],
		]);
	}

	private function importVolumes(?int $id_volume,
								   ?int $id_volume_start): array {

		$results = [];

		// getting the volumes
		$where = '';
		$parameters = [];

		$stmt = $this->pdo->prepare("SELECT * FROM volume {$where} ORDER BY start_year");
		$stmt->execute($parameters);
		$volumes = $stmt->fetchAll();
		foreach($volumes as $volume) {

			// getting volume issues
			$issues = $this->pdo
				->query("SELECT * FROM issue WHERE id_volume = {$volume['id']} ORDER BY number")
				->fetchAll();

			// creating or updating volume
			$params = $volume['disabled'] ? ['date_ignored' => new DateTime()] : [];
			$response = $this->forward('App\Controller\VolumeController::update', [
				'id' => $volume['id_comicvine'],
				'force_update' => false,
				'params' => $params,
				'issues' => $issues,
				//
				'id_force' => $id_volume,
				'id_start' => $id_volume_start,
			]);

			$result = json_decode($response->getContent(), true);
			if(!($result['skipped'] ?? false)) {
				$results[] = $result;
				if($id_volume) {
					break;
				}
			}
		}

		return $results;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	private function importItems(): array {

		$base_url = $_ENV['COMICS_LIBRARY_URL'] ?? null;
		if(!$base_url) {
			throw new Exception('COMICS_LIBRARY_URL missing');
		}

		$results = [];

		$updated_volumes = [];

		// getting the items
		$where = '';
		$parameters = [];

		$stmt = $this->pdo->prepare("SELECT * FROM item {$where} ORDER BY release_date");
		$stmt->execute($parameters);
		$items = $stmt->fetchAll();

		foreach($items as $item) {

//			$item :
//			array:9 [▼
//			  "id" => 1
//			  "id_collection" => 5
//			  "number" => "1"
//			  "title" => "DC Univers Rebirth"
//			  "release_date" => "2017-05-05"
//			  "isbn" => "9791026812531"
//			  "cover" => "/kiosque/justice-league-recit-complet-hs-1-dc-rebirth.jpg"
//			  "notes" => ""
//			  "special" => 1
//			]

			// getting item issues
			$query = "
				SELECT
					item_issue.*,
				   	issue.id_comicvine AS idc_issue,
				   	volume.id_comicvine AS idc_volume
				FROM item_issue
				INNER JOIN issue ON item_issue.id_issue = issue.id
				INNER JOIN volume on issue.id_volume = volume.id
				WHERE item_issue.id_item = :id_item
				ORDER BY item_issue.n, idc_volume";
			$stmt = $this->pdo->prepare($query);
			$stmt->execute(['id_item' => $item['id']]);
			$issues = $stmt->fetchAll();

			foreach($issues as $issue) {

				$this->updateIssueVolume($issue, $updated_volumes);

				// $issue:
//				array:1 [▼
//				  0 => array:5 [▼
//					"id" => 1
//					"id_item" => 1
//					"id_issue" => 172
//					"n" => 1
//					"idc_volume" => "90597"
//				  ]
//				]
			}

			// getting the collection data
			$query = "
				SELECT 
				    collection.*,
				    type.id as type_id,
				    type.name as type_name
				FROM collection
				LEFT JOIN type ON collection.id_type = type.id
				WHERE collection.id = :id_collection
				GROUP BY collection.id";
			$stmt = $this->pdo->prepare($query);
			$stmt->execute(['id_collection' => $item['id_collection']]);
			$collectionData = $stmt->fetch();
//			dd(($collectionData));
//			continue;

//			dd($issues, $item);

			// creating or updating volume
//			$params = $item['disabled'] ? ['date_ignored' => new DateTime()] : [];
			$params = [];
			$response = $this->forward('App\Controller\ItemController::update', [
				'id' => null,
				'force_update' => false,
				'itemData' => $item,
				'collectionData' => $collectionData,
				'params' => $params,
				'issues' => $issues,
			]);

			$result = json_decode($response->getContent(), true);
			if(!($result['skipped'] ?? false)) {
				$results[] = $result;
			}
		}


//		dd($updated_volumes);
//		dd($items);

		return $results;
	}

	private function updateIssueVolume(array $issue, array &$updated_volumes): void {
		if(in_array($issue['idc_volume'], $updated_volumes)) {
			return;
		}

		// update volume
		$response = $this->forward('App\Controller\VolumeController::update', [
			'id' => $issue['idc_volume'],
//			'force_update' => true,
			'interval' => 'P1D',
		]);

		$updated_volumes[] = $issue['idc_volume'];
	}

	/**
	 * @throws Exception
	 */
	private function setPDO() {
		$database = $_ENV['COMICS_LIBRARY_DATABASE'] ?? null;
		$user = $_ENV['COMICS_LIBRARY_USER'] ?? null;
		$password = $_ENV['COMICS_LIBRARY_PASSWORD'] ?? null;
		if(!$database || !$user || !$password) {
			throw new Exception('COMICS_LIBRARY_DATABASE, COMICS_LIBRARY_USER or COMICS_LIBRARY_PASSWORD missing');
		}
		$this->pdo = new PDO($database, $user, $password);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
		$this->pdo->exec('set names utf8mb4');
	}
}
