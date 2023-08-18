<?php

declare(strict_types=1);

namespace App;

use App\DataMapper\DataMapperInterface;
use App\DataMapper\ResultDataMapper;

class ResultCalculationService
{
    public function __construct(private ResultDataMapper $dataMapper)
    {
    }

    public function setDataMapper(DataMapperInterface $dataMapper): self
    {
        $this->dataMapper = $dataMapper;

        return $this;
    }

    public function calculatePlacements(): void
    {
        $this->calculatePlacement(
            $this->dataMapper->getResultsWithPlacements(),
            'overallPlacement'
        );

        $this->calculatePlacementAgeCategory(
            $this->dataMapper->getResultsWithAgeCategory()
        );
    }

    public function calculatePlacementAgeCategory(array $data): void
    {
        foreach ($data as $ageCategory => $results) {
            $this->calculatePlacement($results, 'ageCategoryPlacement');
        }
    }

    public function calculatePlacement(array $data, string $context): void
    {
        uasort($data, fn($a, $b) => $a->getFinishTime() <=> $b->getFinishTime());

        $placement = 1;
        foreach ($data as $datum) {
            $datum->{$context} = $placement;
            $placement++;
        }
    }

    public function setAverageFinishTime(): void
    {
        $this
            ->dataMapper
            ->getRace()
            ->setAverageFinishMedium(
                $this->calculateAverageFinishTime(
                    $this->dataMapper->getResultsWithoutPlacements()
                )
            );

        $this
            ->dataMapper
            ->getRace()
            ->setAverageFinishLong(
                $this->calculateAverageFinishTime(
                    $this->dataMapper->getResultsWithPlacements()
                )
            );
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
