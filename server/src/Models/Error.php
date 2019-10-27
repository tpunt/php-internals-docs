<?php

namespace PHPInternalsDocs\Models;

class Error implements \JsonSerializable
{
    public $message = '';

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function jsonSerialize()
    {
        return ['error' => ['message' => $this->message]];
    }
}
