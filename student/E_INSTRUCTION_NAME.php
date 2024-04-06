<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

enum E_INSTRUCTION_NAME: string
{
    // Memory and function calls
    case MOVE = 'MOVE';
    case CREATEFRAME = 'CREATEFRAME';
    case PUSHFRAME = 'PUSHFRAME';
    case POPFRAME = 'POPFRAME';
    case DEFVAR = 'DEFVAR';
    case CALL = 'CALL';
    case RETURN = 'RETURN';

    // Data stack
    case PUSHS = 'PUSHS';
    case POPS = 'POPS';

    // Math, relations, bool, conversions
    case ADD = 'ADD';
    case SUB = 'SUB';
    case MUL = 'MUL';
    case IDIV = 'IDIV';
    case DIV = 'DIV';
    case LT = 'LT';
    case GT = 'GT';
    case EQ = 'EQ';
    case AND = 'AND';
    case OR = 'OR';
    case NOT = 'NOT';
    case INT2CHAR = 'INT2CHAR';
    case STRI2INT = 'STRI2INT';
    case INT2FLOAT = 'INT2FLOAT';
    case FLOAT2INT = 'FLOAT2INT';

    // IO
    case READ = 'READ';
    case WRITE = 'WRITE';

    // Strings
    case CONCAT = 'CONCAT';
    case STRLEN = 'STRLEN';
    case GETCHAR = 'GETCHAR';
    case SETCHAR = 'SETCHAR';

    // Types
    case TYPE = 'TYPE';

    // Flow control
    case LABEL = 'LABEL';
    case JUMP = 'JUMP';
    case JUMPIFEQ = 'JUMPIFEQ';
    case JUMPIFNEQ = 'JUMPIFNEQ';
    case EXIT = 'EXIT';

    // Debug
    case DPRINT = 'DPRINT';
    case BREAK = 'BREAK';

    // Stack
    case CLEARS = 'CLEARS';

    // Stack - math, relations, bool, conversions
    case ADDS = 'ADDS';
    case SUBS = 'SUBS';
    case MULS = 'MULS';
    case IDIVS = 'IDIVS';
    case DIVS = 'DIVS';
    case LTS = 'LTS';
    case GTS = 'GTS';
    case EQS = 'EQS';
    case ANDS = 'ANDS';
    case ORS = 'ORS';
    case NOTS = 'NOTS';
    case INT2CHARS = 'INT2CHARS';
    case STRI2INTS = 'STRI2INTS';
    case INT2FLOATS = 'INT2FLOATS';
    case FLOAT2INTS = 'FLOAT2INTS';

    // Stack - flow control
    case JUMPIFEQS = 'JUMPIFEQS';
    case JUMPIFNEQS = 'JUMPIFNEQS';

    /**
     * Get instruction name by value
     *
     * @param string $value Instruction name
     * @return E_INSTRUCTION_NAME Instruction name
     * @throws SemanticException If value is not a valid instruction
     */
    public static function fromValue(string $value): E_INSTRUCTION_NAME
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return E_INSTRUCTION_NAME::{$status->name};
            }
        }

        throw new SemanticException("$value is not a valid instruction " . self::class);
    }
}