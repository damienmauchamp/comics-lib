<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Volume;
use App\Repository\IssueRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IssueController extends AbstractController {
	#[Route('/issue/{id<\d+>}', name: 'app_issue')]
	public function index(IssueRepository $issueRepo,
						  int             $id): Response {

		// todo : exception if not found
		$issue = $this->get($id, $issueRepo);

		// todo : render issue
		dd($issue);

		return $this->render('issue/index.html.twig', [
			'controller_name' => 'IssueController',
		]);
	}

	#[Route('/issue/{id<\d+>}/read', name: 'app_issue_read',
		methods: ['GET', 'POST'])]
	public function read(IssueRepository $issueRepo,
						 RequestStack    $requestStack,
						 int             $id,
						 bool            $read = true): JsonResponse {

		// todo : exception if not found
		$issue = $this->get($id, $issueRepo);

		// getting the date
		if($read) {
			$date_read = $requestStack->getCurrentRequest()->get('date_read');
			try {
				$datetime = new DateTime($date_read);
			} catch(Exception $e) {
				$datetime = new DateTime();
			}
		}
		else {
			$datetime = null;
		}

		// update issue
		$issue->setDateRead($datetime);
		$issueRepo->update($issue, true);

		// todo : json response
		dd($issue);
		return new JsonResponse([

		]);
	}

	#[Route('/issue/{id<\d+>}/unread', name: 'app_issue_unread',
		methods: ['GET', 'POST'])]
	public function unread(IssueRepository $issueRepo,
						   RequestStack    $requestStack,
						   int             $id): JsonResponse {
		return $this->read($issueRepo, $requestStack, $id, false);
	}

	#[Route('/issue/{id<\d+>}/ignore', name: 'app_issue_ignore',
		methods: ['GET', 'POST'])]
	public function ignore(IssueRepository $issueRepo,
						   RequestStack    $requestStack,
						   int             $id,
						   bool            $ignore = true): JsonResponse {

		// todo : exception if not found
		$issue = $this->get($id, $issueRepo);

		$ignore = $requestStack->getCurrentRequest()->get('ignore', $ignore);

		// update issue
		$issue->setDateIgnored($ignore ? new DateTime() : null);
		$issueRepo->update($issue, true);

		// todo : json response
		dd($issue);
		return new JsonResponse([

		]);
	}

	#[Route('/issue/{id<\d+>}/unignore', name: 'app_issue_unignore',
		methods: ['GET', 'POST'])]
	public function unignore(IssueRepository $issueRepo,
							 RequestStack    $requestStack,
							 int             $id): JsonResponse {
		return $this->ignore($issueRepo, $requestStack, $id, false);
	}

	private function get(int             $id,
						 IssueRepository $issueRepo = null): Issue {

		$issue = $issueRepo->find($id);
		if(empty($issue)) {
			throw $this->createNotFoundException('No issue found for id '.$id);
		}
		return $issue;
	}
}
