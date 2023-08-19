<?php

declare(strict_types=1);

namespace App\Importer;

use App\AverageFinishTimeService;
use App\DataMapper\ResultDataMapper;
use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use App\ResultCalculationService;
use App\Validator\Csv\CsvFileValidator;
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
        private readonly AverageFinishTimeService $averageFinishTimeService,
        private readonly ResultCalculationService $resultCalculationService,
        private readonly ResultDataMapper $resultDataMapper
    ) {
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function import(array $data): Response
    {
        $race = Race::createFromDto($data['raceDto']);

        $this->raceRepository->save($race);
        $file = Reader::createFromFileObject($data['file']->openFile());
        $file->setHeaderOffset(0);
        $header = $file->getHeader();

        $rowCount = iterator_count($file->getRecords());
        $this->csvFileValidator->validateHeader($header);

        $rowNumber = 1;
        $invalidRows = [];

        foreach ($file->getRecords() as $record) {
            $rowNumber = $this->resultDataMapper->mapRecord($race, $record, $rowNumber, $invalidRows);
        }

        $this->resultRepository->flushAndClear($race);

        $this->averageFinishTimeService->updateAverageFinishTimeForMediumAndLongRaces($race);

        $this->raceRepository->flush();

        $this->updateCalculations($race);

        $response = [
            'race' => $race,
            'message' => [
                'status' => 'Successfully imported the objects',
                'totalNumber' => $rowCount,
                'invalidRows' => count($invalidRows) > 0 ? implode(',', $invalidRows) : 'none',
            ]
        ];

        return new Response($this->serializer->serialize($response, 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\TH:i:s.u\Z',
            JsonEncode::OPTIONS => JSON_PRETTY_PRINT
        ]));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function updateCalculations(Race $race): void
    {
        $this->resultCalculationService->updateOverallPlacements($race);
        $this->resultCalculationService->updateAgeCategoryPlacements($race);
    }
}
