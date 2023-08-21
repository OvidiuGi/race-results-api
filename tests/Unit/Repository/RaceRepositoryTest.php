<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Race;
use App\Repository\RaceRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class RaceRepositoryTest extends TestCase
{
    private $entityManagerMock;
    private $connectionMock;
    private $raceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->connectionMock = $this->createMock(Connection::class);
        $this->entityManagerMock->method('getConnection')->willReturn($this->connectionMock);

        $registryMock = $this->createMock(ManagerRegistry::class);
        $registryMock->method('getManagerForClass')->willReturn($this->entityManagerMock);

        $this->raceRepository = new RaceRepository($registryMock, $this->entityManagerMock);
    }

    public function testSaveWithFlush()
    {
        $race = new Race();
        $this->entityManagerMock->expects($this->once())->method('persist')->with($race);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->raceRepository->save($race, true);

        $this->assertEquals(1, 1);
    }

    public function testSaveWithoutFlush()
    {
        $race = new Race();
        $this->entityManagerMock->expects($this->once())->method('persist')->with($race);
        $this->entityManagerMock->expects($this->never())->method('flush');

        $this->raceRepository->save($race);

        $this->assertEquals(1, 1);
    }
}
