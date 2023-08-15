<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Race;
use App\Importer\ImporterInterface;
use App\Repository\RaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class ImportAction extends AbstractController
{
    public function __construct(
        private ImporterInterface $raceResultsImporter,
        private RaceRepository $raceRepository
    ) {}

    /**
     * @throws \Exception
     */
    #[Route(path: '/import', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('file');
        $title = $request->request->get('raceTitle');
        $date = $request->request->get('raceDate');

        $race = $this->raceRepository->findOneBy([
            'title' => $title,
            'date' => new \DateTimeImmutable($date)
        ]);

        if ($race instanceof Race) {
            return new Response(
                'The race with title: ' . $title .' and date: ' . $date . ' already exists!',
                Response::HTTP_FOUND
            );
        }

        $race = Race::createFromArray([
            'title' => $title,
            'date' => new \DateTimeImmutable($date)
        ]);

        $this->raceResultsImporter->setAdditionalData(['race' => $race]);

        try {
            $response = $this->raceResultsImporter->import($file);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response($response, Response::HTTP_CREATED);
    }
}