<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Race;
use App\Entity\Result;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class ResultRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RaceRepository $raceRepository
    ) {
        parent::__construct($registry, Result::class);
    }

    public function save(Result $result, $flush = false): void
    {
        $this->entityManager->persist($result);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function saveBulk(Race $race, iterable $results, int $batchSize = 1000): void
    {
        $i = 0;

        foreach ($results as $item) {
            $result = new Result();
            $result->fullName = $item->fullName;
            $result->distance = $item->distance;
            $result->setFinishTime($item->getFinishTime());
            $result->ageCategory = $item->ageCategory;
            $result->overallPlacement = $item->overallPlacement;
            $result->ageCategoryPlacement = $item->ageCategoryPlacement;
            $result->setRace($race);

            $this->entityManager->persist($result);

            $result = null;
            if (($i % $batchSize) === 0) {
                $this->flushAndClear($race);
            }
            ++$i;
        }
        $this->flushAndClear($race);

        unset($results);
    }

    private function flushAndClear(Race &$race): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();

        $race = $this->raceRepository->find($race->getId());
    }
}
