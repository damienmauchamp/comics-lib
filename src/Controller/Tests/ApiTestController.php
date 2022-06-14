<?php

namespace App\Controller\Tests;

use App\Service\APIService;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiTestController extends AbstractController {
	#[Route('/api/test', name: 'app_api_test')]
	public function index(APIService $api): Response {
//
//		$res = $api->search('superman', $page);
		return $this->render('api_test/index.html.twig', [
			'controller_name' => 'ApiTestController',
		]);
	}

	#[NoReturn] #[Route('/api/search/{term}/{page<\d+>?1}', name: 'app_api_search')]
	public function search(APIService $api,
						   string     $term,
						   int        $page): void {
		$res = $api->search($term, null, $page);
		dd($res, [
			'info' => 'SEARCH',
			'term' => $term,
			'page' => $page,
		]);
	}

	#[NoReturn] #[Route('/api/search-publisher/{name}/{page<\d+>?1}', name: 'app_api_search_publisher')]
	public function searchPublisher(APIService $api,
									string     $name,
									int        $page): void {
//		$res = $api->searchPublisher($name, $page);
		$res = $api->publishers($name, $page);
		dd($res, [
			'info' => 'SEARCH PUBLISHERS',
			'name' => $name,
			'page' => $page,
		]);
	}

	#[NoReturn] #[Route('/api/search-volume/{name}/{page<\d+>?1}', name: 'app_api_search_volume')]
	public function searchVolume(APIService $api,
								 string     $name,
								 int        $page): void {
		$res = $api->searchVolume($name, $page);
//		$res = $api->volumes($name, $page);
		dd($res, [
			'info' => 'SEARCH VOLUMES',
			'name' => $name,
			'page' => $page,
		]);
	}

	#[NoReturn] #[Route('/api/search-issue/{name}/{page<\d+>?1}', name: 'app_api_search_issue')]
	public function searchIssue(APIService $api,
								string     $name,
								int        $page): void {
		$res = $api->searchIssue($name, $page);
//		$res = $api->issues($name, $page);
		dd($res, [
			'info' => 'SEARCH ISSUES',
			'name' => $name,
			'page' => $page,
		]);
	}

	#[NoReturn] #[Route('/api/publisher/{id<\d+>}', name: 'app_api_publisher')]
	public function publisher(APIService $api,
							  int        $id): void {
		$res = $api->publisher($id);
		dd($res, [
			'info' => 'PUBLISHER',
			'id' => $id,
		]);
	}

	#[NoReturn] #[Route('/api/volume/{id<\d+>}', name: 'app_api_volume')]
	public function volume(APIService $api,
						   int        $id): void {
		$res = $api->volume($id);
		dd($res, [
			'info' => 'VOLUME',
			'id' => $id,
		]);
	}

	#[NoReturn] #[Route('/api/issue/{id<\d+>}', name: 'app_api_issue')]
	public function issue(APIService $api,
						  int        $id): void {
		$res = $api->issue($id);
		dd($res, [
			'info' => 'ISSUE',
			'id' => $id,
		]);
	}
}
