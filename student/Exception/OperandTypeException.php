<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for wrong operand type
 */
class OperandTypeException extends IPPException
{
    public function __construct(string $message = "Wrong operand type", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::OPERAND_TYPE_ERROR, $previous, false);
    }
}
