<?php

declare(strict_types=1);

namespace App\Tests\Unit\Handler;

use App\Entity\Race;
use App\Entity\Result;
use App\Handler\ResultHandler;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use App\Validator\Csv\CsvFileValidator;
use PHPUnit\Framework\TestCase;

class ResultHandlerTest extends TestCase
{
    private $resultRepositoryMock;
    private $csvFileValidatorMock;
    private $raceRepositoryMock;
    private $resultHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultRepositoryMock = $this->createMock(ResultRepository::class);
        $this->csvFileValidatorMock = $this->createMock(CsvFileValidator::class);
        $this->raceRepositoryMock = $this->createMock(RaceRepository::class);

        $this->resultHandler = new ResultHandler(
            10,
            $this->resultRepositoryMock,
            $this->csvFileValidatorMock,
            $this->raceRepositoryMock
        );
    }

    public function testHandleRecordWithValidRow()
    {
        $race = new Race();
        $record = [
            'fullName' => 'value1',
            'distance' => 'value2',
            'finishTime' => '01:01:01',
            'ageCategory' => 'M'
        ];
        $rowNumber = 1;
        $invalidRows = [];

        $this->csvFileValidatorMock->expects($this->once())->method('validateRow')->willReturn(true);

        $this->resultRepositoryMock->expects($this->once())->method('save')->with($this->isInstanceOf(Result::class));
        $this->raceRepositoryMock->expects($this->never())->method('find');

        $this->resultHandler->handleRecord($race, $record, $rowNumber, $invalidRows);

        $this->assertCount(0, $invalidRows);
    }

    public function testHandleRecordWithInvalidRow()
    {
        $race = new Race();
        $record = ['column1' => 'value1', 'column2' => 'value2'];
        $rowNumber = 1;
        $invalidRows = [];

        $this->csvFileValidatorMock->expects($this->once())->method('validateRow')->willReturn(false);

        $this->resultRepositoryMock->expects($this->never())->method('save');
        $this->raceRepositoryMock->expects($this->never())->method('find');

        $this->resultHandler->handleRecord($race, $record, $rowNumber, $invalidRows);

        $this->assertCount(1, $invalidRows);
    }

    public function testHandleRecordWithBatchFlush()
    {
        $race = new Race();
        $record = [
            'fullName' => 'value1',
            'distance' => 'value2',
            'finishTime' => '01:01:01',
            'ageCategory' => 'M'
        ];
        $rowNumber = 10;
        $invalidRows = [];

        $this->csvFileValidatorMock->expects($this->once())->method('validateRow')->willReturn(true);
        $this->resultRepositoryMock->expects($this->once())->method('save')->with($this->isInstanceOf(Result::class));
        $this->resultRepositoryMock->expects($this->once())->method('flushAndClear');
        $this->raceRepositoryMock->expects($this->once())->method('find')->willReturn($race);

        $this->resultHandler->handleRecord($race, $record, $rowNumber, $invalidRows);

        $this->assertCount(0, $invalidRows);
    }
}
