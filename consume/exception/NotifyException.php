<?php

namespace go1\util\consume\exception;

use Exception;
use Monolog\Logger;

class NotifyException extends Exception
{
    protected $message;
    protected $type;

    public function __construct(string $message, string $type = Logger::WARNING)
    {
        parent::__construct($message, $type);
        $this->type = $type;
    }

    public function setNotifyExceptionMessage($message)
    {
        $this->message = $message;
    }

    public function getNotifyExceptionMessage()
    {
        return $this->message;
    }

    public function setNotifyExceptionType($type)
    {
        $this->type = $type;
    }

    public function getNotifyExceptionType()
    {
        return $this->type;
    }
}
