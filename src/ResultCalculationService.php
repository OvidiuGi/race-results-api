<?php

declare(strict_types=1);

namespace App;

use App\Entity\Race;
use App\Entity\Result;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ResultCalculationService
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws Exception
     */
    public function updatePlacementsForResults(Race $race): void
    {
        $sql = sprintf(
            "
            UPDATE result
            JOIN (
                SELECT id, race_id,
                       ROW_NUMBER() OVER (PARTITION BY race_id ORDER BY finish_time) AS placement
                  FROM result
                  WHERE race_id = {$race->getId()} and distance = '%s'
                 ORDER BY race_id, finish_time
            ) AS ranked ON result.id = ranked.id
            SET result.overall_placement = ranked.placement
        ",
            Result::DISTANCE_LONG
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement();
    }

    /**
     * @throws Exception
     */
    public function updateAgeCategoryPlacementsForResults(Race $race): void
    {
        $sql = sprintf(
            "
            UPDATE result
            JOIN (
                SELECT id, race_id, age_category,
                       ROW_NUMBER() OVER (PARTITION BY race_id, age_category ORDER BY finish_time) AS placement
                  FROM result
                  WHERE race_id = {$race->getId()} and distance = '%s'
                 ORDER BY race_id, age_category, finish_time
            ) AS ranked ON result.id = ranked.id
            SET result.age_category_placement = ranked.placement
        ",
            Result::DISTANCE_LONG
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement();
    }
}
