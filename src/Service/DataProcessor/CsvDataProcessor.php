<?php

declare(strict_types=1);

namespace App\Service\DataProcessor;

use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\File;

class CsvDataProcessor implements DataProcessorInterface
{
    public function process(File $file): array
    {
        $processedData = [];
        $file = Reader::createFromFileObject($file->openFile());
        $file->setHeaderOffset(0);
        $header = $file->getHeader();

        foreach ($file->getRecords() as $record) {
            // validare date
            $processedData[] = array_combine($header, $record);
        }

        return $processedData;
    }
}
