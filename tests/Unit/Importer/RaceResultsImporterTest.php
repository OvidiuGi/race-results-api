<?php

declare(strict_types=1);

namespace App\Tests\Unit\Importer;

use App\Dto\RaceDto;
use App\Handler\ResultHandler;
use App\Importer\RaceResultsImporter;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use App\Validator\Csv\CsvFileValidator;
use Doctrine\Migrations\Tools\Console\Exception\FileTypeNotSupported;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class RaceResultsImporterTest extends TestCase
{
    private $serializerMock;
    private $resultRepositoryMock;
    private $raceRepositoryMock;
    private $csvFileValidatorMock;
    private $resultHandlerMock;
    private $entityManagerMock;
    private $raceResultsImporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->resultRepositoryMock = $this->createMock(ResultRepository::class);
        $this->raceRepositoryMock = $this->createMock(RaceRepository::class);
        $this->csvFileValidatorMock = $this->createMock(CsvFileValidator::class);
        $this->resultHandlerMock = $this->createMock(ResultHandler::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->raceResultsImporter = new RaceResultsImporter(
            $this->serializerMock,
            $this->resultRepositoryMock,
            $this->raceRepositoryMock,
            $this->csvFileValidatorMock,
            $this->resultHandlerMock,
            $this->entityManagerMock
        );
    }

    public function testImportWithInvalidFile()
    {
        $data = [
            'file' => new File('tests/Functional/Entity/fixtures/invalid.xml'),
            'raceDto' => new RaceDto('Test', new \DateTimeImmutable()),
        ];

        $this->csvFileValidatorMock->expects($this->once())->method('validateFile')
            ->willThrowException(new FileTypeNotSupported('Invalid file type', Response::HTTP_BAD_REQUEST));

        $this->expectException(FileTypeNotSupported::class);
        $this->raceResultsImporter->import($data);
    }

    public function testImportWithInvalidHeader()
    {
        $data = [
            'file' => new File('tests/Functional/Entity/fixtures/invalidHeader.csv'),
            'raceDto' => new RaceDto('Test', new \DateTimeImmutable()),
        ];

        $this->csvFileValidatorMock->expects($this->once())->method('validateFile');
        $this->csvFileValidatorMock->expects($this->once())->method('validateHeader')
            ->willThrowException(new \InvalidArgumentException('Required fields missing', Response::HTTP_BAD_REQUEST));

        $this->expectException(\InvalidArgumentException::class);
        $this->raceResultsImporter->import($data);
    }
}
