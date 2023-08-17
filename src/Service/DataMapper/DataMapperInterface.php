<?php

declare(strict_types=1);

namespace App\Service\DataMapper;

interface DataMapperInterface
{
    public function getData(): array;
    public function map(array $data): self;

    public function getRowCount(): int;
}
