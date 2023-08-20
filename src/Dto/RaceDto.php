<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RaceDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,
        #[Assert\Type(\DateTimeImmutable::class)]
        public \DateTimeImmutable $date
    ) {
    }
}
