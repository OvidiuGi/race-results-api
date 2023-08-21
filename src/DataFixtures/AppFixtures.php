<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Story\DefaultRaceStory;
use App\DataFixtures\Story\DefaultResultStory;
use App\Entity\Race;
use App\Entity\Result;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly RaceRepository $raceRepository,
        private readonly ResultRepository $resultRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        DefaultRaceStory::load();
        DefaultResultStory::load();

        $races = $this->raceRepository->findAll();

        /** @var Race $race */
        foreach ($races as $race) {
            $race->setAverageFinishMedium(
                $this->raceRepository->getAverageFinishTime($race, Result::DISTANCE_MEDIUM)
            );
            $race->setAverageFinishLong(
                $this->raceRepository->getAverageFinishTime($race, Result::DISTANCE_LONG)
            );
            $this->resultRepository->calculateOverallPlacements($race);
            $this->resultRepository->calculateAgeCategoryPlacements($race);

            $manager->flush();
        }
    }
}
