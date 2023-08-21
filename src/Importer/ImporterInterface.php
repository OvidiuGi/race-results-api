<?php

declare(strict_types=1);

namespace App\Importer;

use Symfony\Component\HttpFoundation\Response;

interface ImporterInterface
{
    public function import(array $data): Response;
}
