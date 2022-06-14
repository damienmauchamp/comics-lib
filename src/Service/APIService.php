<?php

namespace App\Service;

use Exception;

use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @link https://comicvine.gamespot.com/api/documentation
 */
class APIService {

	protected string $url = "https://comicvine.gamespot.com/api";
	protected string $format = 'json';
	protected int $limit = 100;
	protected array $ids = [
		'volume' => '4050',
		'issue' => '4000',
		'publisher' => '4010',
	];
	protected ?string $token;

	//
	private array $params = [];

	public function __construct(protected HttpClientInterface $client,
								protected Security            $security,
								protected LoggerInterface     $logger) {
		$this->client->withOptions([
			'base_uri' => $this->url,
			'headers' => [
				'Content-Type' => 'application/json',
				'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
			]
		]);

		$this->token = $_ENV['API_KEY'] ?? null;
	}

	/**
	 * @param bool $limit
	 * @param int|null $page
	 * @return void
	 */
	private function init(bool $limit = true,
						  ?int $page = null): void {
		$this->params = [
			'api_key' => $this->token,
			'format' => $this->format,
		];
		if($limit) {
			// limit
			$this->params['limit'] = $this->limit;
			if($page) {
				// page
//				$this->params['offset'] = ($page - 1) * $this->limit;
				$this->params['page'] = $page;
			}
		}
	}

	#[ArrayShape(['error' => "bool", 'message' => "string", 'code' => "null", 'data' => "array", 'api' => "array"])]
	private function error(string $message = '', ?array $data = null): array {
		return [
			'error' => true,
			'message' => $message,
			'code' => null,
			'data' => $data ?? [],
			'api' => [],
		];
	}

	private function response(ResponseInterface $response): array {
		try {
			$code = $response->getStatusCode();
		} catch(TransportExceptionInterface $e) {
			$code = null;
		}

		$data = [
			'error' => false,
			'message' => null,
			'code' => $code,
			'data' => [],
			'api' => [
				'error' => null,
				'limit' => null,
				'offset' => null,
				'number_of_page_results' => null,
				'number_of_total_results' => null,
				'status_code' => null,
				'results' => null,
				'version' => null,
			]
		];

		try {
			// request is successful
			$array = $response->toArray();
			$data['api'] = $array;
			$data['data'] = $array['results'];
			$data['error'] = false;

			if($array['error'] !== 'OK') {
				// object not found
				$data['error'] = true;
				$data['message'] = $array['error'];
			}
		} catch(ClientExceptionInterface $e) {
			// 4xx errors
			$data['api'] = json_decode($response->getContent(), true);
			$data['data'] = [];
			$data['error'] = true;
			$data['message'] = $data['api']['results'] ?? null;
		} catch(DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface|Exception $e) {
			// 5xx errors & other errors
			$data['api'] = null;
			$data['data'] = [];
			$data['error'] = true;
			$data['message'] = $e->getMessage();
		}

		return $data;
	}

	/**
	 * @param string $endpoint
	 * @param string $method
	 * @return array
	 */
	private function request(string $endpoint, string $method = 'GET'): array {

		//
		$endpoint = ltrim($endpoint, '/ ');
		$url = $this->url."/{$endpoint}";
		$params = [
			'query' => $this->params,
			// 'body' => [ post params ],
			// ... headers
		];

		// TODO : API logger
		$this->logger->info("[API] $method /{$endpoint} ", ['params' => $params]);

		try {
			// make request
			$response = $this->client->request($method, $url, $params);
			return $this->response($response);
		} catch(TransportExceptionInterface $e) {
			return $this->error($e->getMessage());
		}
	}

	//
	private function addResourcesType(?string $resource = null): void {
		if($resource) {
			$this->checkResourceType($resource);
			$this->params['resources'] = $resource;
		}
	}

	private function addFieldList(array $field_list = []): void {
		if($field_list) {
			$this->params['field_list'] = $field_list;
//			$this->params['field_list'] = implode(',', $field_list);
		}
	}

	////////////
	////////////
	////////////

	/**
	 * @link https://comicvine.gamespot.com/api/documentation#toc-0-30
	 * @param string|null $term
	 * @param string|null $resource
	 * @param int $page
	 * @param string|null $endpoint Custom endpoint
	 * @param array $filters
	 * @return array
	 */
	public function search(?string $term,
						   ?string $resource = null,
						   int     $page = 1,
						   ?string $endpoint = null,
						   array   $filters = []): array {

		$this->init(true, $page);

		// adding search term
		if($term) {
			$this->params['query'] = $term;
		}

		// adding resource type if there's one
		$this->addResourcesType($resource);

		// adding field list
//		if(!$endpoint) {
//			$this->addFieldList(['id', 'image', 'publisher', 'name', 'start_year', 'count_of_issues']);
//		}

		// adding filters
		if($filters) {
			$this->params['filter'] = implode(',', array_map(static function ($field, $value) {
				return "{$field}:{$value}";
			}, array_keys($filters), $filters));;
		}

		// request
		return $this->request($endpoint ?? 'search', 'GET');
	}

	/**
	 * @link https://comicvine.gamespot.com/api/documentation#toc-0-26
	 * @param int $id
	 * @return array
	 */
	public function publisher(int $id): array {

		$this->init(false);

		// adding field list
//		$this->addFieldList(['id', 'image']);

		// request
		return $this->request("publisher/{$this->ids['publisher']}-{$id}", 'GET');
	}

	/**
	 * @link https://comicvine.gamespot.com/api/documentation#toc-0-27
	 * @param string $name
	 * @param int $page
	 * @return array
	 */
	public function publishers(string $name, int $page = 1): array {

		$this->init(false);

		// adding field list
//		$this->addFieldList(['id', 'image', 'publisher', 'name', 'start_year', 'count_of_issues']);

		// request
		return $this->search(null, null, $page, 'publishers', [
			'name' => $name,
		]);
	}

	/**
	 * @link https://comicvine.gamespot.com/api/documentation#toc-0-42
	 * @param int $id
	 * @return array
	 */
	public function volume(int $id): array {

		$this->init(false);

		// adding field list
//		$this->addFieldList(['id', 'image', 'publisher', 'name', 'start_year', 'count_of_issues']);

		// request
		return $this->request("volume/{$this->ids['volume']}-{$id}", 'GET');
	}

	/**
	 * @link https://comicvine.gamespot.com/api/documentation#toc-0-43
	 * @param string $name
	 * @param int $page
	 * @return array
	 */
	public function volumes(string $name, int $page = 1): array {

		$this->init(false);

		// adding field list
//		$this->addFieldList(['id', 'image', 'publisher', 'name', 'start_year', 'count_of_issues']);

		// request
		return $this->search(null, null, $page, 'volumes', [
			'name' => $name,
		]);
	}

	/**
	 * @link https://comicvine.gamespot.com/api/documentation#toc-0-10
	 * @param int $id
	 * @return array
	 */
	public function issue(int $id): array {

		$this->init(false);

		// adding field list
//		$this->addFieldList(['id', 'image', 'publisher', 'name', 'start_year', 'count_of_issues']);

		// request
		return $this->request("issue/{$this->ids['issue']}-{$id}", 'GET');
	}

	/**
	 * @link https://comicvine.gamespot.com/api/documentation#toc-0-11
	 * @param string $name
	 * @param int $page
	 * @return array
	 */
	public function issues(string $name, int $page = 1): array {

		$this->init(false);

		// adding field list
//		$this->addFieldList(['id', 'image', 'publisher', 'name', 'start_year', 'count_of_issues']);

		// request
		return $this->search(null, null, $page, 'issues', [
			'name' => $name,
		]);
	}

	////////////
	////////////
	////////////

	public function searchPublisher(string $term, int $page = 1): array {
		return $this->search($term, 'publisher', $page);
	}

	public function searchVolume(string $term, int $page = 1): array {
		return $this->search($term, 'volume', $page);
	}

	public function searchIssue(string $term, int $page = 1): array {
		return $this->search($term, 'issue', $page);
	}

	////////////
	////////////
	////////////

	/**
	 * Check if
	 * @param string $resource
	 * @return void
	 * @throws Exception
	 */
	private function checkResourceType(string $resource): void {
		$resources = [
			'character',
			'concept',
			'origin',
			'object',
			'location',
			'issue',
			'story_arc',
			'volume',
			'publisher',
			'person',
			'team',
			'video',
		];
		if(!in_array($resource, $resources)) {
			throw new Exception("Invalid resource type");
		}
	}

//	protected function get(string $chemin, array $params = []): array {
//		$url = $this->setUrl($chemin, $params);
//		return $this->requete('GET', $url, $this->getHeaders());
//	}
//
//	protected function post(string $chemin, array $params = []): array {
//		$url = $this->setUrl($chemin);
//		return $this->requete('POST', $url, array_merge($this->getHeaders(), ['body' => $params]));
//	}
}