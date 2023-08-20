<?php

declare(strict_types=1);

namespace App\Exception;

class RaceResultHandlingException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        $message = sprintf($this->message, $message);

        parent::__construct($message, $code, $previous);
    }

    public $message = 'Error importing race results: %s';
}
