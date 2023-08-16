<?php

namespace App\Exception\CustomException;

class RaceResultHandlingException extends \Exception
{
    public $message = 'Error handling race results: %s';
}
