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
            if (!isset($value)) {
                return '';
            }

            $value = preg_replace_callback('/\\\\[0-9]{3}/', function ($match) {
                return mb_chr(intval(substr($match[0], 1)));
            }, $value);
        }

        if ($type === E_ARGUMENT_TYPE::BOOL) {
            if (!isset($value)) {
                return false;
            }

            if ($value !== 'true' && $value !== 'false') {
                throw new SemanticException("Invalid boolean value '$value'");
            }

            $value = $value === 'true';
        }

        if ($type === E_ARGUMENT_TYPE::INT) {
            if (!isset($value)) {
                return 0;
            }

            if (!is_numeric($value)) {
                throw new SemanticException("Invalid integer value '$value'");
            }

            $value = (int)$value;
        }

        if ($type === E_ARGUMENT_TYPE::NIL) {
            return null;
        }

        return $value;
    }

    /**
     * Get string value for typed value
     *
     * @param E_ARGUMENT_TYPE $type Type of the value
     * @param int|string|bool|null $value Value to cast
     * @return string String value for typed value
     */
    public static function getTypedValueString(E_ARGUMENT_TYPE $type, int|string|bool|null $value): string
    {
        if ($type === E_ARGUMENT_TYPE::STRING) {
            if (!isset($value)) {
                return '';
            }
        }

        if ($type === E_ARGUMENT_TYPE::BOOL) {
            if (!isset($value)) {
                return 'false';
            }

            $value = $value ? 'true' : 'false';
        }

        if ($type === E_ARGUMENT_TYPE::INT) {
            if (!isset($value)) {
                return '0';
            }

            $value = (string)$value;
        }

        if ($type === E_ARGUMENT_TYPE::NIL) {
            return '';
        }

        return $value;
    }

    public static function determineValueType(int|string|bool|null $value): E_ARGUMENT_TYPE
    {
        if (is_string($value)) {
            return E_ARGUMENT_TYPE::STRING;
        }

        if (is_int($value)) {
            return E_ARGUMENT_TYPE::INT;
        }

        if (is_bool($value)) {
            return E_ARGUMENT_TYPE::BOOL;
        }

        return E_ARGUMENT_TYPE::NIL;
    }
}