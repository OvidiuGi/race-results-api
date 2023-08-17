<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\RaceDto;
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

    public function findByRaceDto(RaceDto $raceDto): null|object
    {
        return $this->findOneBy([
            'title' => $raceDto->title,
            'date' => $raceDto->getDate(),
        ]);
    }
}
