<?php

declare(strict_types=1);

namespace App\Exception;

class DuplicateRaceException extends \Exception
{
    public function __construct(
        string $title,
        \DateTimeImmutable $date,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = sprintf($this->message, $title, $date->format('Y-m-d'));
        parent::__construct($message, $code, $previous);
    }

    public $message = 'The race with title: %s and date: %s already exists!';
}
