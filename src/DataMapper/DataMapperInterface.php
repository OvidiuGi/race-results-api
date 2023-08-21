<?php

declare(strict_types=1);

namespace App\DataMapper;

use App\Entity\Race;

interface DataMapperInterface
{
    public function mapRecord(Race &$race, array $record, int $rowNumber, array &$invalidRows): void;
}
