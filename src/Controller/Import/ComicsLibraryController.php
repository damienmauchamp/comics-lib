<?php

namespace App\Controller\Import;

use App\Controller\VolumeController;
use DateTime;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComicsLibraryController extends AbstractController {
	/**
	 * Import from Comics Library
	 * @link https://github.com/damienmauchamp/comics-library
	 * @param ManagerRegistry $doctrine
	 * @return JsonResponse
	 */
	#[Route('/import/comics-library', name: 'app_import_comics_library')]
	public function index(ManagerRegistry $doctrine,
						  RequestStack    $requestStack): JsonResponse {

		$database = $doctrine->getManager()->getConnection();
//		dd($test->query('SELECT count(*) FROM issue')->fetchAll());

		set_time_limit(0);
//		$id_volume_param = $requestStack->getCurrentRequest()->get('id_volume', null);
//		$id_volume_start_param = $requestStack->getCurrentRequest()->get('id_volume_start', null);
//		$id_volume = null;
//		$id_volume_start = null;
		$id_volume = $requestStack->getCurrentRequest()->get('id_volume', null);
		$id_volume_start = $requestStack->getCurrentRequest()->get('id_volume_start', null);

//		// trying to find
//		$database->query('SELECT count(*) FROM issue')->fetch();


		$pdo = $_ENV['COMICS_LIBRARY_DATABASE'] ?? null;
		$user = $_ENV['COMICS_LIBRARY_USER'] ?? null;
		$password = $_ENV['COMICS_LIBRARY_PASSWORD'] ?? null;
		if(!$pdo || !$user || !$password) {
			return new JsonResponse([
				'status' => 'error',
				'message' => 'COMICS_LIBRARY_DATABASE, COMICS_LIBRARY_USER or COMICS_LIBRARY_PASSWORD is not set',
			]);
		}
		$pdo = new PDO($pdo, $user, $password);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

		$results = ['volumes' => [], 'items' => []];

		// getting the volumes
		$where = '';
		$parameters = [];
//		if($id_volume) {
//			$where = 'WHERE id = :id_volume';
//			$parameters = [':id_volume' => $id_volume];
//		}
//		else if($id_volume_start) {
//			$where = 'WHERE id >= :id_volume';
//			$parameters = [':id_volume' => $id_volume];
//		}

		$stmt = $pdo->prepare("SELECT * FROM volume {$where} ORDER BY start_year");
		$stmt->execute($parameters);
		$volumes = $stmt->fetchAll();
		foreach($volumes as $volume) {

			// getting volume issues
			$issues = $pdo
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
				$results['volumes'][] = $result;
				if($id_volume) {
					break;
				}
			}

//			$response->getContent();

//			dd($response->getContent()->toArray());

//			break;
		}

		return new JsonResponse([
			'status' => 'success',
			'message' => 'The volume has been updated',
			'info' => [
				'volumes' => count($results['volumes']),
				'items' => count($results['items']),
			],
			'data' => $id_volume ? $results : [],
		]);
//		dd($results);


		// getting Comics Library database url
//		$conn = DriverManager::getInstance()->openConnection('mysql://username:password@localhost/database', 'connection 2', false);
//		$conn = $this->getEntityManager()->getConnection();
//		$conn = Doctrine_Manager::connection('mysql://username:password@localhost/test', 'connection 1');
//		$this->getDoctrine()->openConnection('mysql://username:password@localhost/test', 'connection 1');
//		return $this->render('import/comics_library/index.html.twig', [
//			'controller_name' => 'ComicsLibraryController',
//		]);
	}
}
