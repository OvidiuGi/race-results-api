<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Race;
use App\Entity\Result;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
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

    /**
     * @throws Exception
     */
    public function flushAndClear(Race &$race): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();

        $race = $this->raceRepository->find($race->getId());
    }
}
