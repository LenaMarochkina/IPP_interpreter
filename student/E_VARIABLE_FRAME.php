<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

enum E_VARIABLE_FRAME: string
{
    case GF = 'GF';
    case LF = 'LF';
    case TF = 'TF';

    public static function containsValue(string $value): bool
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return true;
            }
        }
        return false;
    }

    public static function fromValue(string $value): E_VARIABLE_FRAME
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return E_VARIABLE_FRAME::{$status->name};
            }
        }
        throw new SemanticException("$value is not a valid instruction " . self::class);
    }
}