<?php

declare(strict_types=1);

namespace App\Importer;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use App\Service\DataMapper\DataMapperInterface;
use App\Service\DataProcessor\DataProcessorInterface;
use App\Service\ResultCalculationService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RaceResultsImporter implements ImporterInterface
{
    public function __construct(
        private DataProcessorInterface   $csvDataProcessor,
        private DataMapperInterface      $resultDataMapper,
        private ResultRepository         $resultRepository,
        private RaceRepository           $raceRepository,
        private ValidatorInterface       $validator,
        private ResultCalculationService $resultCalculationService,
        private SerializerInterface $serializer,
        public array                     $data = []
    ) {}

    public function setAdditionalData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function import(File $file): string
    {
        $race = $this->data['race'];

        $processedData = $this->csvDataProcessor->process($file);
        $mappedData = $this->resultDataMapper->map($processedData);

        $this->validator->validate($race);
        foreach ($mappedData as $result) {
            $this->validator->validate($result);
        }

        $this->resultCalculationService->calculatePlacements($mappedData);
        $this->resultCalculationService->setAverageFinishTime($race);
        $this->raceRepository->save($race, true);

        foreach ($mappedData as $result) {
            $result->setRace($race);
        }
        $this->resultRepository->saveAll($mappedData, true);

        return $this->serializer->serialize([$race, $mappedData], 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\TH:i:s.u\Z',
            JsonEncode::OPTIONS => JSON_PRETTY_PRINT
        ]);
    }
}
