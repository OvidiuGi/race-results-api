<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class ImportRaceResults extends AbstractController
{
    public function __construct(
//        private ImportResultsHandler $importResultsHandler
    ) {}

    #[Route(path: '/import', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $raceTitle = $request->get('raceTitle');
        $raceDate = new \DateTimeImmutable($request->get('raceDate'));

        $file = $request->files->get('file');
//        $this->importResultsHandler->handle($race);

        return new Response('test', 301);
    }
}