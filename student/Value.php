<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

/**
 * Class Value
 *
 * Represents a value of an argument or a variable. Used to store type casting in a single place.
 */
class Value
{
    private string|null $value;

    public function __construct(string|null $value)
    {
        $this->value = $value;
    }

    /**
     * Get pure value
     *
     * @return string Value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get typed value
     *
     * @param E_ARGUMENT_TYPE $type Type to cast the value to
     * @return int|string|bool|null Typed value
     * @throws SemanticException If type is not a string, int or bool
     */
    public function getTypedValue(E_ARGUMENT_TYPE $type): int|string|bool|null
    {
        if (!$type->isLiteralType()) {
            throw new SemanticException("Invalid variable type '$type->value' [{$type->name}]");
        }

        return match ($type) {
            E_ARGUMENT_TYPE::INT => (int)$this->value,
            E_ARGUMENT_TYPE::STRING => $this->value,
            E_ARGUMENT_TYPE::BOOL => $this->value === 'true',
            default => null,
        };
    }
}