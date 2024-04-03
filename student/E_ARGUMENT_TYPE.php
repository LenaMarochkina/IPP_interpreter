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

    public function isVariableType(): bool
    {
        return in_array($this, [self::INT, self::STRING, self::BOOL]);
    }

    public static function containsValue(string $value): bool
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return true;
            }
        }
        return false;
    }

    public static function fromValue(string $value): string
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return $status->name;
            }
        }
        throw new SemanticException("$value is not a valid instruction " . self::class);
    }
}