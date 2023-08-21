<?php

declare(strict_types=1);

namespace App\Importer;

use App\Handler\ResultHandler;
use App\Entity\Race;
use App\Entity\Result;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use App\Validator\Csv\CsvFileValidator;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RaceResultsImporter implements ImporterInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ResultRepository $resultRepository,
        private readonly RaceRepository $raceRepository,
        private readonly CsvFileValidator $csvFileValidator,
        private readonly ResultHandler $resultHandler,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function import(array $data): Response
    {
        $this->csvFileValidator->validateFile($data['file']);

        $file = Reader::createFromFileObject($data['file']->openFile());
        $file->setHeaderOffset(0);

        $this->csvFileValidator->validateHeader($file->getHeader());

        $race = Race::createFromDto($data['raceDto']);
        $this->raceRepository->save($race, true);

        $rowCount = 1;
        $invalidRows = [];

        foreach ($file->getRecords() as $record) {
            $this->resultHandler->handleRecord($race, $record, $rowCount, $invalidRows);
            $rowCount++;
        }

        $this->entityManager->flush();

        $this->setAverageFinishTimes($race);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->calculatePlacements($race);

        $response = [
            'race' => $race,
            'message' => [
                'status' => 'Successfully imported the results',
                'totalNumber' => $rowCount - 1,
                'invalidRows' => count($invalidRows) > 0 ? implode(',', $invalidRows) : 'none',
            ]
        ];

        return new Response($this->serializer->serialize($response, 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\TH:i:s.u\Z',
            JsonEncode::OPTIONS => JSON_PRETTY_PRINT
        ]), Response::HTTP_CREATED);
    }

    private function setAverageFinishTimes(Race $race): void
    {
        $race->setAverageFinishMedium(
            $this->raceRepository->getAverageFinishTime(
                $race,
                Result::DISTANCE_MEDIUM
            )
        );
        $race->setAverageFinishLong(
            $this->raceRepository->getAverageFinishTime(
                $race,
                Result::DISTANCE_LONG
            )
        );
    }

    private function calculatePlacements(Race $race): void
    {
        $this->resultRepository->calculateOverallPlacements($race);
        $this->resultRepository->calculateAgeCategoryPlacements($race);
    }
}
