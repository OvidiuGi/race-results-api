<?php

declare(strict_types=1);

namespace App\Importer;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Service\DataMapper\DataMapperInterface;
use App\Service\DataProcessor\DataProcessorInterface;
use App\Service\ResultCalculationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RaceResultsImporter implements ImporterInterface
{
    public function __construct(
        private DataProcessorInterface $csvDataProcessor,
        private DataMapperInterface $resultDataMapper,
        private RaceRepository $raceRepository,
        private ValidatorInterface $validator,
        private ResultCalculationService $resultCalculationService,
        private SerializerInterface $serializer,
        public array $data = []
    ) {
    }

    public function setAdditionalData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function import(array $data): Response
    {
        $race = Race::createFromDto($data['raceDto']);

        $processedData = $this->csvDataProcessor->process($data['file']);
        $mappedData = $this->resultDataMapper->map($processedData);

        foreach ($mappedData as $result) {
            $race->addResult($result);
        }

        $this->validator->validate($race);
        foreach ($mappedData as $result) {
            $this->validator->validate($result);
        }

        $this->resultCalculationService->calculatePlacements($mappedData);
        $this->resultCalculationService->setAverageFinishTime($race);
        $this->raceRepository->save($race, true);

        return new Response($this->serializer->serialize([$race, $mappedData], 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\TH:i:s.u\Z',
            JsonEncode::OPTIONS => JSON_PRETTY_PRINT
        ]));
    }
}
