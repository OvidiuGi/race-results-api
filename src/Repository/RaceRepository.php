<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class RaceRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct($registry, Race::class);
    }

    public function save(Race $race, $flush = false): void
    {
        $this->entityManager->persist($race);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
