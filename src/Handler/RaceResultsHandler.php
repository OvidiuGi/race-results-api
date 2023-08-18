<?php

declare(strict_types=1);

namespace App\Handler;

use App\Exception\DuplicateRaceException;
use App\Exception\RaceResultHandlingException;
use App\Importer\ImporterInterface;
use App\Repository\RaceRepository;
use Symfony\Component\HttpFoundation\Response;

readonly class RaceResultsHandler
{
    public function __construct(private RaceRepository $raceRepository, private ImporterInterface $raceResultsImporter)
    {
    }

    /**
     * @throws DuplicateRaceException
     * @throws RaceResultHandlingException
     */
    public function handle(array $data): Response
    {
        $race = $this->raceRepository->findOneBy([
                'title' => $data['raceDto']->title,
                'date' => $data['raceDto']->date,
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
