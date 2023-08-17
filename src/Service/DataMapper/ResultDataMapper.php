<?php

declare(strict_types=1);

namespace App\Service\DataMapper;

use App\Entity\Race;
use App\Entity\Result;

class ResultDataMapper implements DataMapperInterface
{
    public function __construct(
        private array $data = [],
        private array $resultsWithPlacement = [],
        private array $resultsWithoutPlacement = [],
        private array $resultsWithAgeCategory = [],
        private int $rowCount = 0
    ) {
    }

    public function map(array $data): self
    {
        $this->data['race'] = $data['race'];

        foreach ($data['processedData'] as $item) {
            $result = Result::createFromArray($item);
            $result->setRace($this->data['race']);
            $this->rowCount++;

            if ($result->distance === Result::DISTANCE_LONG) {
                $this->resultsWithPlacement[] = $result;
                $this->resultsWithAgeCategory[$result->ageCategory][] = $result;
            } else {
                $this->resultsWithoutPlacement[] = $result;
            }
        }

        return $this;
    }

    public function getData(): array
    {
        return array_merge($this->resultsWithPlacement, $this->resultsWithoutPlacement);
    }

    public function getResultsWithPlacements(): array
    {
        return $this->resultsWithPlacement;
    }

    public function getResultsWithAgeCategory(): array
    {
        return $this->resultsWithAgeCategory;
    }

    public function getResultsWithoutPlacements(): array
    {
        return $this->resultsWithoutPlacement;
    }

    public function getRace(): Race
    {
        return $this->data['race'];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
