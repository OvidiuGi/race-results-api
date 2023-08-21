<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Race;
use App\Entity\Result;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class ResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Result::class);
    }

    public function save(Result $result, $flush = false): void
    {
        $this->entityManager->persist($result);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function flushAndClear(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @throws Exception
     */
    public function calculateOverallPlacements(Race $race): void
    {
        $connection = $this->entityManager->getConnection();

        $sql = "
        UPDATE result
        JOIN (
            SELECT
                id,
                ROW_NUMBER() OVER (PARTITION BY race_id ORDER BY finish_time) AS placement
            FROM result
            WHERE race_id = :race_id AND distance = :distance
        ) AS ranked ON result.id = ranked.id
        SET result.overall_placement = ranked.placement
        WHERE result.race_id = :race_id
    ";

        $params = [
            'race_id' => $race->getId(),
            'distance' => Result::DISTANCE_LONG,
        ];

        $statement = $connection->prepare($sql);
        $statement->executeStatement($params);
    }

    public function calculateAgeCategoryPlacements(Race $race): void
    {
        $connection = $this->entityManager->getConnection();

        $sql = "
            UPDATE result
            JOIN (
                SELECT id, race_id, age_category,
                       ROW_NUMBER() OVER (PARTITION BY race_id, age_category ORDER BY finish_time) AS placement
                  FROM result
                  WHERE race_id = :race_id and distance = :distance
                 ORDER BY race_id, age_category, finish_time
            ) AS ranked ON result.id = ranked.id
            SET result.age_category_placement = ranked.placement
            WHERE result.race_id = :race_id
        ";

        $params = [
            'race_id' => $race->getId(),
            'distance' => Result::DISTANCE_LONG,
        ];

        $stmt = $connection->prepare($sql);
        $stmt->executeStatement($params);
    }
}
