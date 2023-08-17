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
        private readonly RaceRepository $raceRepository,
        private readonly ImporterInterface $raceResultsImporter
    ) {
    }

    /**
     * @throws DuplicateRaceException
     * @throws RaceResultHandlingException
     */
    public function handle(array $data): Response
    {
        $race = $this->raceRepository->findByRaceDto($data['raceDto']);

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
