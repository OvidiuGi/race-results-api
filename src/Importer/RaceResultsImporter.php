<?php

declare(strict_types=1);

namespace App\Importer;

use App\DataMapper\DataMapperInterface;
use App\DataProcessor\DataProcessorInterface;
use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use App\ResultCalculationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RaceResultsImporter implements ImporterInterface
{
    public function __construct(
        private readonly DataProcessorInterface $csvDataProcessor,
        private readonly DataMapperInterface $resultDataMapper,
        private readonly ResultCalculationService $resultCalculationService,
        private readonly SerializerInterface $serializer,
        private readonly ResultRepository $resultRepository,
        private readonly RaceRepository $raceRepository
    ) {
    }

    public function import(array $data): Response
    {
        $race = Race::createFromDto($data['raceDto']);

        $processedData = $this->csvDataProcessor->process($data['file']);
        $mappedData = $this->resultDataMapper->map(['processedData' => $processedData, 'race' => $race]);

        $this->resultCalculationService->setDataMapper($mappedData);
        $this->resultCalculationService->calculatePlacements();
        $this->resultCalculationService->setAverageFinishTime();

        $this->raceRepository->save($race, true);
        $this->resultRepository->saveBulk($mappedData->getRace(), $mappedData->getData());

        $response = [
            'race' => $race,
            'message' => 'Successfully imported the objects', 'totalNumber' => $mappedData->getRowCount()
        ];

        return new Response($this->serializer->serialize($response, 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\TH:i:s.u\Z',
            JsonEncode::OPTIONS => JSON_PRETTY_PRINT
        ]));
    }
}
