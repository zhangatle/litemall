<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class BusinessException extends Exception
{
    public function __construct($codeResponse, $info = '', Throwable $previous = null)
    {
        list($code, $message) = $codeResponse;
        parent::__construct($info ?: $message, $code, $previous);
    }
}
