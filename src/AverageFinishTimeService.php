<?php

declare(strict_types=1);

namespace App;

use App\Entity\Race;
use App\Entity\Result;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class AverageFinishTimeService
{
    public function __construct(private readonly Connection $connection, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws Exception
     */
//    public function updateAverageFinishTimeForMediumAndLongRaces(Race $race): void
//    {
//        $sql = sprintf(
//            "
//            UPDATE race AS r
//            SET r.average_finish_medium = (
//                SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(finish_time)))
//                FROM result
//                WHERE race_id = {$race->getId()} AND distance = '%s'
//            ),
//            r.average_finish_long = (
//                SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(finish_time)))
//                FROM result
//                WHERE race_id = {$race->getId()} AND distance = '%s'
//            )
//            WHERE r.id = {$race->getId()}
//        ",
//            Result::DISTANCE_MEDIUM,
//            Result::DISTANCE_LONG
//        );
//
//        $stmt = $this->connection->prepare($sql);
//        $stmt->executeStatement();
//    }

    public function updateAverageFinishTimeForMediumAndLongRaces(Race &$race): void
    {
        $mediumAvg = $this->calculateAverageFinishTime($race, Result::DISTANCE_MEDIUM);
        $longAvg = $this->calculateAverageFinishTime($race, Result::DISTANCE_LONG);

        $race->setAverageFinishMedium($mediumAvg);
        $race->setAverageFinishLong($longAvg);

        $this->entityManager->persist($race);
        $this->entityManager->flush();
    }

    private function calculateAverageFinishTime(Race $race, string $distance): ?\DateTimeImmutable
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('AVG(r.finishTime) as averageFinishTime')
            ->from(Result::class, 'r')
            ->where('r.race = :raceId')
            ->andWhere('r.distance = :distance')
            ->setParameter('raceId', $race->getId())
            ->setParameter('distance', $distance);

        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        if ($result === null) {
            return null;
        }

        // Convert seconds to DateInterval
        return (new \DateTimeImmutable())->setTimestamp((int)$result);
    }

    public function updateAverageFinishTimeForLongRace(Race $race): void
    {
        $sql = sprintf(
            "
            UPDATE race AS r
            SET r.average_finish_long = (
                SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(finish_time)))
                FROM result
                WHERE race_id = {$race->getId()} AND distance = '%s'
            )
            WHERE r.id = {$race->getId()}
        ",
            Result::DISTANCE_LONG
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement();
    }
}
