<?php

namespace App\Controller;

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

	#[NoReturn] #[Route('/api/search-publisher/{term}/{page<\d+>?1}', name: 'app_api_search_publisher')]
	public function searchPublisher(APIService $api,
									string     $term,
									int        $page): void {
		$res = $api->searchPublisher($term, $page);
		dd($res, [
			'info' => 'SEARCH PUBLISHERS',
			'term' => $term,
			'page' => $page,
		]);
	}

	#[NoReturn] #[Route('/api/search-volume/{term}/{page<\d+>?1}', name: 'app_api_search_volume')]
	public function searchVolume(APIService $api,
								 string     $term,
								 int        $page): void {
		$res = $api->searchVolume($term, $page);
		dd($res, [
			'info' => 'SEARCH VOLUMES',
			'term' => $term,
			'page' => $page,
		]);
	}

	#[NoReturn] #[Route('/api/search-issue/{term}/{page<\d+>?1}', name: 'app_api_search_issue')]
	public function searchIssue(APIService $api,
								string     $term,
								int        $page): void {
		$res = $api->searchIssue($term, $page);
		dd($res, [
			'info' => 'SEARCH ISSUES',
			'term' => $term,
			'page' => $page,
		]);
	}
}
