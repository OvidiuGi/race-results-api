<?php

declare(strict_types=1);

namespace App\EventListener;

use App\AverageFinishTimeService;
use App\Entity\Result;
use App\Repository\ResultRepository;
use App\ResultCalculationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Result::class)]
class ResultUpdatedListener
{
    public function __construct(
        private readonly AverageFinishTimeService $averageFinishTimeService,
        private readonly ResultCalculationService $resultCalculationService,
        private readonly ResultRepository $resultRepository
    ) {
    }

    public function postUpdate(Result $result, PostUpdateEventArgs $event): void
    {
        if ($result->distance !== Result::DISTANCE_LONG) {
            return;
        }

        $changedPlacement = $this->isPlacementChanged($result);

        if ($changedPlacement === 'both' || $changedPlacement === 'overall') {
            $this->resultCalculationService->updateOverallPlacements($result->getRace());
        }

        if ($changedPlacement === 'both' || $changedPlacement === 'ageCategory') {
            $this->resultCalculationService->updateAgeCategoryPlacements($result->getRace());
        }

        $this->averageFinishTimeService->updateAverageFinishTimeForLongRace($result->getRace());

        $objectManager = $event->getObjectManager();
        $objectManager->flush();
    }

    private function isPlacementChanged(Result $data): string
    {
        if ($this->compareOverall($data)) {
            return 'overall';
        }

        if ($this->compareAgeCategory($data)) {
            return 'ageCategory';
        }

        return 'none';
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

        if ($previous && $previous->getFinishTime() >= $current->getFinishTime()) {
            return true;
        }

        if ($next && $next->getFinishTime() <= $current->getFinishTime()) {
            return true;
        }

        return false;
    }

    private function compareOverall(Result $current): bool
    {
        return $this->comparePlacements($current, 'overall');
    }

    private function compareAgeCategory(Result $current): bool
    {
        return $this->comparePlacements($current, 'ageCategory');
    }
}
