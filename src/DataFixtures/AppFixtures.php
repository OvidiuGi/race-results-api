<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\AverageFinishTimeService;
use App\Entity\Race;
use App\Entity\Result;
use App\ResultCalculationService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly AverageFinishTimeService $averageFinishTimeService,
        private readonly ResultCalculationService $resultCalculationService
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

            $manager->persist($race);
            $manager->flush();

            $this->setPlacementsAndAverageFinishTime($race);
        }
    }

    /**
     * @throws Exception
     */
    private function setPlacementsAndAverageFinishTime(Race $race): void
    {
        $this->resultCalculationService->updateOverallPlacements($race);
        $this->resultCalculationService->updateAgeCategoryPlacements($race);
        $this->averageFinishTimeService->updateAverageFinishTimeForMediumAndLongRaces($race);
    }
}
