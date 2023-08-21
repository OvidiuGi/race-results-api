<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Race;

interface HandlerInterface
{
    public function handleRecord(Race &$race, array $record, int $rowNumber, array &$invalidRows): void;
}
