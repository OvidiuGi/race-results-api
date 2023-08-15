<?php

declare(strict_types=1);

namespace App\Importer;

use App\Entity\Race;
use Symfony\Component\HttpFoundation\File\File;

interface ImporterInterface
{
    public function import(File $file): string;

    public function setAdditionalData(array $data): self;
}