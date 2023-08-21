<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class RaceRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct($registry, Race::class);
    }

    public function save(Race $race, $flush = false): void
    {
        $this->entityManager->persist($race);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function getAverageFinishTime(Race $race, string $distance): ?\DateTimeImmutable
    {
        $connection = $this->entityManager->getConnection();

        $sql = "
            SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(finish_time)))
            FROM result
            WHERE race_id = :raceId AND distance = :distance
        ";

        $result = $connection->executeQuery($sql, [
            'raceId' => $race->getId(),
            'distance' => $distance,
        ])->fetchOne();

        return new \DateTimeImmutable($result);
    }
}
