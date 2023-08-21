<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Result;
use App\Repository\ResultRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class ResultRepositoryTest extends TestCase
{
    private $entityManagerMock;
    private $connectionMock;
    private $resultRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->connectionMock = $this->createMock(Connection::class);
        $this->entityManagerMock->method('getConnection')->willReturn($this->connectionMock);

        $registryMock = $this->createMock(ManagerRegistry::class);
        $registryMock->method('getManagerForClass')->willReturn($this->entityManagerMock);

        $this->resultRepository = new ResultRepository($registryMock, $this->entityManagerMock);
    }

    public function testSaveWithFlush()
    {
        $result = new Result();
        $this->entityManagerMock->expects($this->once())->method('persist')->with($result);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->resultRepository->save($result, true);
    }

    public function testSaveWithoutFlush()
    {
        $result = new Result();
        $this->entityManagerMock->expects($this->once())->method('persist')->with($result);
        $this->entityManagerMock->expects($this->never())->method('flush');

        $this->resultRepository->save($result, false);
    }

    public function testFlushAndClear()
    {
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->entityManagerMock->expects($this->once())->method('clear');

        $this->resultRepository->flushAndClear();
    }
}
