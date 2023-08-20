<?php

declare(strict_types=1);

namespace App\DataFixtures;

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
        for ($i = 0; $i <= 10; $i++) {
            $race = new Race();
            $race->title = 'Race ' . $i;
            $race->setDate(new \DateTimeImmutable());

            for ($j = 0; $j <= 10; $j++) {
                $result = new Result();
                $result->fullName = 'Runner ' . $j . ' Race ' . $i;
                $result->distance = $j % 2 === 0 ? Result::DISTANCE_MEDIUM : Result::DISTANCE_LONG;
                $result->ageCategory = $j % 2 === 0 ? 'M20-25' : 'F20-25';
                $result->setFinishTime(new \DateTimeImmutable("+{$j} minutes"));
                $result->setRace($race);

                $manager->persist($result);
            }

            $race->setAverageFinishLong(
                $this->raceRepository->getAverageFinishTime(
                    $race,
                    Result::DISTANCE_LONG
                )
            );
            $race->setAverageFinishMedium(
                $this->raceRepository->getAverageFinishTime(
                    $race,
                    Result::DISTANCE_MEDIUM
                )
            );

            $this->resultRepository->calculateOverallPlacements($race);
            $this->resultRepository->calculateAgeCategoryPlacements($race);

            $manager->persist($race);
            $manager->flush();
        }
    }
}
