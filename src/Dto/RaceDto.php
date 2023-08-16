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
        private \DateTimeImmutable $date
    ) {
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
