<?php

namespace App\Controller;

use App\Repository\ImprintRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImprintController extends AbstractController {
	#[Route('/imprint/{id<\d+>}', name: 'app_imprint')]
	public function index(ImprintRepository $imprintRepository,
						  int               $id): Response {

		$imprint = $imprintRepository->find($id);

		if(!$imprint) {
			throw $this->createNotFoundException('No imprint found for id '.$id);
		}

		dd($imprint);

//		return $this->render('imprint/index.html.twig', [
//			'imprint' => $imprint,
//		]);
	}
}
