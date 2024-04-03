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

        $value = $this->value;

        if ($type === E_ARGUMENT_TYPE::STRING) {
            $value = preg_replace_callback('/\\\\[0-9]{3}/', function ($match) {
                return mb_chr(substr($match[0], 1));
            }, $value);
        }

        if ($type === E_ARGUMENT_TYPE::BOOL) {
            if ($value !== 'true' && $value !== 'false') {
                throw new SemanticException("Invalid boolean value '$value'");
            }

            $value = $value === 'true';
        }

        if ($type === E_ARGUMENT_TYPE::INT) {
            if (!is_numeric($value)) {
                throw new SemanticException("Invalid integer value '$value'");
            }

            $value = (int)$value;
        }

        return $value;
    }
}