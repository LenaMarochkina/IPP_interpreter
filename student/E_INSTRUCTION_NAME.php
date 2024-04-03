<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

enum E_INSTRUCTION_NAME: string
{
    case MOVE = 'MOVE';
    case CREATEFRAME = 'CREATEFRAME';
    case PUSHFRAME = 'PUSHFRAME';
    case POPFRAME = 'POPFRAME';
    case DEFVAR = 'DEFVAR';
    case CALL = 'CALL';
    case RETURN = 'RETURN';
    case PUSHS = 'PUSHS';
    case POPS = 'POPS';
    case ADD = 'ADD';
    case SUB = 'SUB';
    case MUL = 'MUL';
    case IDIV = 'IDIV';
    case LT = 'LT';
    case GT = 'GT';
    case EQ = 'EQ';
    case AND = 'AND';
    case OR = 'OR';
    case NOT = 'NOT';
    case INT2CHAR = 'INT2CHAR';
    case STRI2INT = 'STRI2INT';
    case READ = 'READ';
    case WRITE = 'WRITE';
    case CONCAT = 'CONCAT';
    case STRLEN = 'STRLEN';
    case GETCHAR = 'GETCHAR';
    case SETCHAR = 'SETCHAR';
    case TYPE = 'TYPE';
    case LABEL = 'LABEL';
    case JUMP = 'JUMP';
    case JUMPIFEQ = 'JUMPIFEQ';
    case JUMPIFNEQ = 'JUMPIFNEQ';
    case EXIT = 'EXIT';
    case DPRINT = 'DPRINT';
    case BREAK = 'BREAK';

    public static function fromName(string $name): string
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status->value;
            }
        }
        throw new SemanticException("$name is not a valid instruction " . self::class);
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