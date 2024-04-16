<?php

namespace IPP\Student;

use IPP\Core\Exception\OutputFileException;
use IPP\Student\Core\StreamWriter;
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
     * @return string|null Value
     */
    public function getValue(): string|null
    {
        return $this->value;
    }

    /**
     * Get typed value
     *
     * @param E_ARGUMENT_TYPE $type Type to cast the value to
     * @return float|int|string|bool|null Typed value
     * @throws SemanticException If type is not a string, int or bool
     */
    public function getTypedValue(E_ARGUMENT_TYPE $type): float|int|string|bool|null
    {
        if (!$type->isLiteralType()) {
            throw new SemanticException("Invalid variable type '$type->value' [$type->name]");
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

        if ($type === E_ARGUMENT_TYPE::FLOAT) {
            if (!isset($value)) {
                return 0.0;
            }

            if (!is_string($value)) {
                throw new SemanticException("Invalid float value '$value'");
            }

            $result = FloatHelpers::parseInputFloat($value) ?? floatval($value);

            $value = $result;
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
     * @param float|int|string|bool|null $value Value to cast
     * @return string String value for typed value
     */
    public static function getTypedValueString(E_ARGUMENT_TYPE $type, float|int|string|bool|null $value): string
    {
        $string_value = '';

        if ($type === E_ARGUMENT_TYPE::STRING) {
            if (!isset($value)) {
                return $string_value;
            }

            $string_value = (string)$value;
        }

        if ($type === E_ARGUMENT_TYPE::BOOL) {
            if (!isset($value)) {
                return 'false';
            }

            $string_value = $value ? 'true' : 'false';
        }

        if ($type === E_ARGUMENT_TYPE::INT) {
            if (!isset($value)) {
                return '0';
            }

            $string_value = (string)$value;
        }

        if ($type === E_ARGUMENT_TYPE::FLOAT) {
            if (!isset($value)) {
                return '0.0';
            }

            $string_value = (string)$value;
        }

        return $string_value;
    }

    /**
     * Determine type of the value
     *
     * @param float|int|string|bool|null $value Value to determine type of
     * @return E_ARGUMENT_TYPE Type of the value
     */
    public static function determineValueType(float|int|string|bool|null $value): E_ARGUMENT_TYPE
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

        if (is_float($value)) {
            return E_ARGUMENT_TYPE::FLOAT;
        }

        return E_ARGUMENT_TYPE::NIL;
    }

    /**
     * Get string value for STDOUT
     *
     * @throws SemanticException If type is not a string, int or bool
     * @throws OutputFileException If output file cannot be opened
     */
    public function outputValueForSTDOUT(E_ARGUMENT_TYPE $type): string
    {
        $stream = fopen('php://memory', 'r+');

        if (!$stream) {
            throw new OutputFileException('Cannot open output');
        }

        $outputWriter = new StreamWriter($stream);

        if ($type === E_ARGUMENT_TYPE::VAR) {
            return '-';
        }

        $typedValue = $this->getTypedValue($type);

        switch ($type) {
            case E_ARGUMENT_TYPE::INT:
                $outputWriter->writeInt((int)$typedValue);
                break;
            case E_ARGUMENT_TYPE::BOOL:
                $outputWriter->writeBool((bool)$typedValue);
                break;
            case E_ARGUMENT_TYPE::STRING:
                $outputWriter->writeString((string)$typedValue);
                break;
            case E_ARGUMENT_TYPE::FLOAT:
                $outputWriter->writeFloat((float)$typedValue);
                break;
            default:
                break;
        }

        rewind($stream);
        $outputValue = stream_get_contents($stream);
        fclose($stream);

        if ($outputValue === false || strlen($outputValue) === 0)
            $outputValue = '-';

        return $outputValue;
    }
}