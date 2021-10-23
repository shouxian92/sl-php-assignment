<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $message;

    public function __construct($message) {
        $this->message = $message;
    }

    public function render()
    {
        return response(['error' => $this->message], 404);
    }
}