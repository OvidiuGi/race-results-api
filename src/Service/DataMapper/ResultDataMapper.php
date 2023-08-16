<?php

declare(strict_types=1);

namespace App\Service\DataMapper;

use App\Entity\Result;

class ResultDataMapper implements DataMapperInterface
{
    public function map(array $data): array
    {
        $mappedData = [];

        foreach ($data as $item) {
            $mappedData[] = Result::createFromArray($item);
        }

        return $mappedData;
    }
}
