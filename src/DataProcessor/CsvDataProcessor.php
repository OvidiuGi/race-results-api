<?php

declare(strict_types=1);

namespace App\DataProcessor;

use App\Validator\Csv\CsvFileValidator;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\File;

class CsvDataProcessor implements DataProcessorInterface
{
    public function __construct(private CsvFileValidator $csvFileValidator)
    {
    }

    /**
     * @throws Exception
     */
    public function process(File $file): array
    {
        $processedData = [];

        $this->csvFileValidator->validateFile($file);

        $file = Reader::createFromFileObject($file->openFile());
        $file->setHeaderOffset(0);
        $header = $file->getHeader();

        // verify if the header is valid and has the required fields
        $this->csvFileValidator->validateHeader($header);
        $rowNumber = 1;
        foreach ($file->getRecords() as $record) {
            $row = array_combine($header, $record);
            $this->csvFileValidator->validateRow($row, $rowNumber);
            // validare date
            $processedData[] = $row;
            $rowNumber++;
        }

        return $processedData;
    }
}
