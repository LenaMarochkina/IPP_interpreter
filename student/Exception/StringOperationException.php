<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for wrong operand type
 */
class StringOperationException extends IPPException
{
    public function __construct(string $message = "String operation type", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::STRING_OPERATION_ERROR, $previous, false);
    }
}
