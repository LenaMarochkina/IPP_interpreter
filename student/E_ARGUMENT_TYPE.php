<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

enum E_ARGUMENT_TYPE: string
{
    case VAR = 'var';
    case LABEL = 'label';
    case INT = 'int';
    case STRING = 'string';
    case BOOL = 'bool';
    case TYPE = 'type';
    case NIL = 'nil';

    /**
     * Check if enum is literal type
     *
     * @return bool True if enum is literal type, false otherwise
     */
    public function isLiteralType(): bool
    {
        return in_array($this, [self::INT, self::STRING, self::BOOL, self::NIL]);
    }

    /**
     * Check if enum contains value
     *
     * @param string $value Enum value
     * @return bool True if enum contains value, false otherwise
     */
    public static function containsValue(string $value): bool
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get enum value from string
     *
     * @param string $value Enum value
     * @return E_ARGUMENT_TYPE Enum value
     * @throws SemanticException If value is not a valid type
     */
    public static function fromValue(string $value): E_ARGUMENT_TYPE
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return E_ARGUMENT_TYPE::{$status->name};
            }
        }
        throw new SemanticException("$value is not a valid type " . self::class);
    }
}