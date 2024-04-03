<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for semantic errors
 */
class SemanticException extends IPPException
{
    public function __construct(string $message = "Some semantic error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::SEMANTIC_ERROR, $previous, false);
    }
}
