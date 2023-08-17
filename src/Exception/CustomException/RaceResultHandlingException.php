<?php

namespace App\Exception\CustomException;

class RaceResultHandlingException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf($this->message, $message);

        parent::__construct($message, $code, $previous);
    }

    public $message = 'Error importing race results: %s';
}
