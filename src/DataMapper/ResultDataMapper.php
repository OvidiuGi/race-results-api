<?php

declare(strict_types=1);

namespace App\DataMapper;

use App\Entity\Race;
use App\Entity\Result;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use App\Validator\Csv\CsvFileValidator;

class ResultDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly int $batchSize,
        private readonly ResultRepository $resultRepository,
        private readonly CsvFileValidator $csvFileValidator,
        private readonly RaceRepository $raceRepository
    ) {
    }

    public function mapRecord(Race &$race, array $record, int $rowNumber, array &$invalidRows): void
    {
        if (!$this->csvFileValidator->validateRow($record, $rowNumber)) {
            $invalidRows[] = $rowNumber;

            return;
        }

        $result = Result::createFromArray($record);
        $result->setRace($race);
        $this->resultRepository->save($result);

        if (($rowNumber % $this->batchSize) === 0) {
            $this->resultRepository->flushAndClear();
            $race = $this->raceRepository->find($race->getId());
        }
    }
}
