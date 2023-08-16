<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Race;
use App\Entity\Result;

class ResultCalculationService
{
    public function __construct(
        public array $resultsWithPlacement = [],
        public array $resultsWithoutPlacement = []
    ) {
    }

    private function filterData(array $data): self
    {
        $this->resultsWithPlacement = array_filter($data, fn($result) =>
            $result->distance === Result::DISTANCE_LONG);

        $this->resultsWithoutPlacement = array_filter($data, fn($result) =>
            $result->distance === Result::DISTANCE_MEDIUM);

        return $this;
    }

    public function calculatePlacements(array $data): void
    {
        $this->filterData($data);

        $resultsWithAgeCategory = [];
        $this->calculateOverallPlacement($this->resultsWithPlacement);

        foreach ($this->resultsWithPlacement as $result) {
            $resultsWithAgeCategory[$result->ageCategory][] = $result;
        }

        foreach ($resultsWithAgeCategory as $ageCategory => $results) {
            $this->calculateAgeCategoryPlacement($results);
        }
    }

    private function calculateOverallPlacement(array &$data): void
    {
        uasort($data, fn($a, $b) => $a->getFinishTime() <=> $b->getFinishTime());

        $placement = 1;
        foreach ($data as $datum) {
            $datum->overallPlacement = $placement;
            $placement++;
        }
    }

    private function calculateAgeCategoryPlacement(array &$data): void
    {
        uasort($data, fn($a, $b) => $a->getFinishTime() <=> $b->getFinishTime());

        $placement = 1;
        foreach ($data as $datum) {
            $datum->ageCategoryPlacement = $placement;
            $placement++;
        }
    }

    public function setAverageFinishTime(Race $race): void
    {
        $race->setAverageFinishMedium($this->calculateAverageFinishTime($this->resultsWithoutPlacement));
        $race->setAverageFinishLong($this->calculateAverageFInishTime($this->resultsWithPlacement));
    }

    private function calculateAverageFinishTime(array $data): \DateTimeImmutable
    {
        $result = 0;

        foreach ($data as $object) {
            $result += $object->getFinishTime()->getTimestamp();
        }

        return new \DateTimeImmutable('@' . (int) ($result / count($data)));
    }
}
