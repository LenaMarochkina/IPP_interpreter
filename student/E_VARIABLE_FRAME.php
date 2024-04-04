<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

enum E_VARIABLE_FRAME: string
{
    case GF = 'GF';
    case LF = 'LF';
    case TF = 'TF';

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
     * @return E_VARIABLE_FRAME Enum value
     * @throws SemanticException If value is not a valid frame
     */
    public static function fromValue(string $value): E_VARIABLE_FRAME
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return E_VARIABLE_FRAME::{$status->name};
            }
        }
        throw new SemanticException("$value is not a valid frame " . self::class);
    }
}