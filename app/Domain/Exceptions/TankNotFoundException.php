<?php

namespace App\Domain\Exceptions;

use Exception;

class TankNotFoundException extends Exception
{
    public function __construct(string $message = "Tanque no encontrado", int $code = 404)
    {
        parent::__construct($message, $code);
    }
}