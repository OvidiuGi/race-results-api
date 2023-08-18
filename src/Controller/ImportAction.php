<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RaceDto;
use App\Exception\DuplicateRaceException;
use App\Importer\RaceResultsImporter;
use App\Repository\RaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class ImportAction extends AbstractController
{
    public function __construct(
        private readonly RaceRepository $raceRepository,
        private readonly RaceResultsImporter $raceResultsImporter
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(#[MapRequestPayload] RaceDto $raceDto, Request $request): Response
    {
        $file = $request->files->get('file');

        try {
            $race = $this->raceRepository->findOneBy([
                'title' => $raceDto->title,
                'date' => $raceDto->date,
            ]);

            if ($race != null) {
                throw new DuplicateRaceException($race->title, $race->getDate());
            }

            return $this->raceResultsImporter->import(['file' => $file, 'raceDto' => $raceDto]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
