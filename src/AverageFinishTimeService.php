<?php

declare(strict_types=1);

namespace App;

use App\Entity\Race;
use App\Entity\Result;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class AverageFinishTimeService
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws Exception
     */
    public function updateAverageFinishTimeForMediumAndLongRaces(Race $race): void
    {
        $sql = sprintf(
            "
            UPDATE race AS r 
            SET r.average_finish_medium = (
                SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(finish_time)))
                FROM result
                WHERE race_id = {$race->getId()} AND distance = '%s'
            ),
            r.average_finish_long = (
                SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(finish_time)))
                FROM result
                WHERE race_id = {$race->getId()} AND distance = '%s'
            )
            WHERE r.id = {$race->getId()}
        ",
            Result::DISTANCE_MEDIUM,
            Result::DISTANCE_LONG
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement();
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
