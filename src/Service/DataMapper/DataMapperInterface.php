<?php

declare(strict_types=1);

namespace App\Service\DataMapper;

interface DataMapperInterface
{
    public function map(array $data): array;
}