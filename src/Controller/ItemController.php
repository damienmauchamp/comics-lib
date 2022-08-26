<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Entity\ItemIssue;
use App\Entity\Type;
use App\Entity\Volume;
use App\Repository\IssueRepository;
use App\Repository\ItemCollectionRepository;
use App\Repository\ItemIssueRepository;
use App\Repository\ItemRepository;
use App\Repository\PublisherRepository;
use App\Repository\TypeRepository;
use App\Repository\VolumeRepository;
use App\Service\APIService;
use DateInterval;
use DateTime;
use Doctrine\ORM\Exception\EntityManagerClosed;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController {
	#[Route('/item/{id<\d+>}', name: 'app_item')]
	public function index(ItemRepository $itemRepository,
						  int            $id): Response {

		$item = $itemRepository->find($id);

		if(!$item) {
			throw $this->createNotFoundException('No item found for id '.$id);
		}
		$item->setIssuesProgress();

		dd($item);

		return $this->render('item/index.html.twig', [
			'controller_name' => 'ItemController',
		]);
	}


	/**
	 * @todo remove GET
	 */
	#[NoReturn] #[Route('/items/update', name: 'app_volume_update',
		methods: ['GET', 'POST'])]
	public function update(ManagerRegistry          $doctrine,
						   APIService               $api,
						   RequestStack             $requestStack,
						   ItemRepository           $itemRepo,
						   ItemIssueRepository      $itemIssueRepo,
						   ItemCollectionRepository $itemCollectionRepo,
						   IssueRepository          $issueRepo,
						   TypeRepository           $itemCollectionTypeRepo,
						   ?int                     $id,
						   array                    $itemData = [],
						   array                    $collectionData = [],
						   array                    $params = [],
						   array                    $issues = []): JsonResponse {

		$force_update = $requestStack->getCurrentRequest()->get('force_update', true);

		// todo : move
		$imgFolder = 'images/';
		$imgDir = $this->getParameter('kernel.project_dir').'/public/'.$imgFolder;
		$imgItemDir = $imgDir.'items/';

		// checking if the item exists
		$item = $itemRepo->findOneBy(['isbn' => $itemData['isbn']]);
		if(empty($item) && $itemData['release_date']) {
			$item = $itemRepo->findOneBy([
				'title' => $itemData['title'],
				'number' => $itemData['number'],
				'release_date' => new DateTime($itemData['release_date']),
			]);
		}
		if(empty($item)) {
			// if not, we create it
			$item = new Item();
			$item->setDateAdded(new DateTime());
		}
//		else if($item->getDateUpdated() > (new DateTime())->sub(new DateInterval('PT30M'))) {
		else if($item->getDateUpdated() > (new DateTime())->sub(new DateInterval('PT1H30M'))) {
			// if the item was updated in the last 30 minutes, we don't update it
			return new JsonResponse(['status' => 'skipped']);
		}

		// trying to find the collection, if not found, create it (string comparaison)
		$id_collection_type = $collectionData['type_id'];
		$name_collection_type = $collectionData['type_name'];

		$tmpType = new Type();
		$tmpType->setId($id_collection_type);
		$tmpType->setName($name_collection_type);
		$collection = $itemCollectionRepo->findOneBy([
			'name' => $collectionData['name'],
			'type' => $tmpType,
		]);

		if(empty($collection)) {
			$collection = new ItemCollection();
			$collection->setOfficial(true);
		}
		$collection->setName($collectionData['name']);

		// getting the type
		$itemCollectionType = $itemCollectionTypeRepo->findOneBy(['name' => $name_collection_type]);
		if(empty($itemCollectionType)) {
			$itemCollectionType = new Type();
			$itemCollectionType->setName($name_collection_type);
			$itemCollectionTypeRepo->add($itemCollectionType, true);
		}

		$collection->setType($itemCollectionType);
//		$itemCollectionRepo->add($collection, true);
		$itemCollectionRepo->add($collection);

		// updating the item
		/** @var Item $item */
		$item->setItemCollection($collection);
		$item->setNumber($itemData['number']);
		$item->setTitle($itemData['title']);
		$item->setReleaseDate(new DateTime($itemData['release_date']));
		$item->setIsbn($itemData['isbn']);
		if(!empty($itemData['cover'])) {

			$currentImg = $item->getImage();
			$imgUrl = $_ENV['COMICS_LIBRARY_URL'].'/'.
				trim($_ENV['COMICS_LIBRARY_IMG_DIR'] ?? '', ' /').'/'.
				trim($itemData['cover'], ' /');

			try {
				$imgName = $item->createImageName();
				if(!is_dir($imgItemDir)) {
					mkdir($imgItemDir, 0777, true);
				}
				$imgPath = $imgItemDir.$imgName;

				try {
					$newImg = copy($imgUrl, $imgPath);
				} catch(Exception $e) {
					// http
					$imgUrl = str_replace('https://', 'http://', $imgUrl);
					$newImg = copy($imgUrl, $imgPath);
				}

				if($currentImg && is_file($imgPath) && is_file($imgItemDir.$currentImg)) {
					unlink($imgItemDir.$currentImg);
				}

				if(is_file($imgPath)) {
					$item->setImage($imgName);
				}
			} catch(Exception $e) {
				// do nothing
				dd($e->getMessage(), $imgUrl, $e);
			}
		}

		$item->setNotes($itemData['notes']);
		$item->setSpecial($itemData['special']);
		$item->setDateUpdated(new DateTime());
//		$itemRepo->add($item, true);
		$itemRepo->add($item);

		// adding issues
		$itemIssues = [];
		foreach($issues as $issueData) {
//			dd($issueData);
			// getting the issue
			$issue = $issueRepo->findOneBy(['idc' => $issueData['idc_issue']]);
//			try {

			$itemIssue = $itemIssueRepo->findOneBy(['item' => $item, 'issue' => $issue]);
			if(empty($itemIssue)) {
				$itemIssue = new ItemIssue();
				$itemIssue->setItem($item);
				$itemIssue->setIssue($issue);
			}
			$itemIssue->setNumber($issueData['n']);
//			$itemIssueRepo->add($itemIssue, true);
			$itemIssueRepo->add($itemIssue);
			$itemIssues[] = $itemIssue;
//				$issue->addItem($item);
//				$item->addIssue($issue);
//			} catch(Exception $e) {
//				dd($e->getMessage(), $issueData, $item);
//			}
//			$issueRepo->add($issue, true);
		}
//		try {
//			$itemRepo->add($item, true);
//		} catch(Exception $e) {
//			dd($e->getMessage(), $item);
//		}

//		dd($item, $itemIssues);

//		dd(
//			$imgFolder.$item->getImage(),
//			$package->getUrl($imgFolder.$item->getImage()),
//			$collectionData, $itemCollectionType, $collection, $item);

		$doctrine->getManager()->flush();

		return new JsonResponse([
			'status' => 'success',
			'message' => 'The item has been updated',
			'item' => [
				'collection' => [
					'id' => $item->getItemCollection()->getId(),
					// ...
				],
				'itemIssues' => $itemIssues,
				'date_added' => $item->getDateAdded()->format('Y-m-d H:i:s'),
				'date_updated' => $item->getDateUpdated()->format('Y-m-d H:i:s'),
				'date_ignored' => $item->getDateIgnored()?->format('Y-m-d H:i:s'),
			],
		]);
	}
}
