<?php

declare(strict_types=1);

namespace App\DataProcessor;

use Symfony\Component\HttpFoundation\File\File;

interface DataProcessorInterface
{
    public function process(File $file): array;
}
