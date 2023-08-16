<?php

declare(strict_types=1);

namespace App\Service\Handler;

use App\Exception\CustomException\DuplicateRaceException;
use App\Exception\CustomException\RaceResultHandlingException;
use App\Importer\ImporterInterface;
use App\Repository\RaceRepository;
use Symfony\Component\HttpFoundation\Response;

class RaceResultsHandler
{
    public function __construct(
        private RaceRepository $raceRepository,
        private ImporterInterface $raceResultsImporter
    ) {
    }

    public function handle(array $data): Response
    {
        $race = $this->raceRepository->findOneBy([
            'title' => $data['raceDto']->title,
            'date' => $data['raceDto']->getDate()
        ]);

        if ($race) {
            throw new DuplicateRaceException($race->title, $race->getDate());
        }

        try {
            return $this->raceResultsImporter->import($data);
        } catch (\Exception $e) {
            throw new RaceResultHandlingException($e->getMessage());
        }
    }
}
