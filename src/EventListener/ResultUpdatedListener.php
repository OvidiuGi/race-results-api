<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Result;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Result::class)]
class ResultUpdatedListener
{
    public function __construct(
        private readonly ResultRepository $resultRepository,
        private readonly RaceRepository $raceRepository
    ) {
    }

    public function postUpdate(Result $result, PostUpdateEventArgs $event): void
    {
        if ($result->distance !== Result::DISTANCE_LONG) {
            return;
        }

        $changedPlacement = $this->isPlacementChanged($result);

        if ($changedPlacement === 'both' || $changedPlacement === 'overall') {
            $this->resultRepository->calculateOverallPlacements($result->getRace());
        }

        if ($changedPlacement === 'both' || $changedPlacement === 'ageCategory') {
            $this->resultRepository->calculateAgeCategoryPlacements($result->getRace());
        }

        $result
            ->getRace()
            ->setAverageFinishLong(
                $this->raceRepository->getAverageFinishTime(
                    $result->getRace(),
                    Result::DISTANCE_LONG
                )
            );

        $objectManager = $event->getObjectManager();
        $objectManager->flush();
    }

    private function isPlacementChanged(Result $data): string|null
    {
        if ($this->compareOverall($data) && $this->compareAgeCategory($data)) {
            return 'both';
        }

        if ($this->compareOverall($data)) {
            return 'overall';
        }

        if ($this->compareAgeCategory($data)) {
            return 'ageCategory';
        }

        return null;
    }

    private function compareOverall(Result $current): bool
    {
        $previous = $this->resultRepository->findOneBy([
            'overallPlacement' => $current->overallPlacement - 1,
        ]);

        $next = $this->resultRepository->findOneBy([
            'overallPlacement' => $current->overallPlacement + 1,
        ]);

        if ($previous && $previous->getFinishTime()->format('H:i:s') >= $current->getFinishTime()->format('H:i:s')) {
            return true;
        }

        if ($next && $next->getFinishTime()->format('H:i:s') <= $current->getFinishTime()->format('H:i:s')) {
            return true;
        }

        return false;
    }

    private function compareAgeCategory(Result $current): bool
    {
        $previous = $this->resultRepository->findOneBy([
            'ageCategoryPlacement' => $current->ageCategoryPlacement - 1,
            'ageCategory' => $current->ageCategory
        ]);

        $next = $this->resultRepository->findOneBy([
            'ageCategoryPlacement' => $current->overallPlacement + 1,
            'ageCategory' => $current->ageCategory
        ]);

        if ($previous && $previous->getFinishTime()->format('H:i:s') >= $current->getFinishTime()->format('H:i:s')) {
            return true;
        }

        if ($next && $next->getFinishTime()->format('H:i:s') <= $current->getFinishTime()->format('H:i:s')) {
            return true;
        }

        return false;
    }

    private function comparePlacements(Result $current, string $placementType): bool
    {
        $placementProperty = $placementType === 'overall' ? 'overallPlacement' : 'ageCategoryPlacement';
        $placementValue = $placementType === 'overall' ? $current->overallPlacement : $current->ageCategoryPlacement;

        $previous = $this->resultRepository->findOneBy([
            $placementProperty => $placementValue - 1,
            'ageCategory' => $current->ageCategory
        ]);

        $next = $this->resultRepository->findOneBy([
            $placementProperty => $placementValue + 1,
            'ageCategory' => $current->ageCategory
        ]);

        if ($previous && $previous->getFinishTime()->format('H:i:s') >= $current->getFinishTime()->format('H:i:s')) {
            return true;
        }

        if ($next && $next->getFinishTime()->format('H:i:s') <= $current->getFinishTime()->format('H:i:s')) {
            return true;
        }

        return false;
    }
}
