<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Result;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class ResultRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct($registry, Result::class);
    }

    public function save(Result $result, $flush = false): void
    {
        $this->entityManager->persist($result);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function saveAll(array $results, $flush = false): void
    {
        foreach ($results as $result) {
            $this->save($result);
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
