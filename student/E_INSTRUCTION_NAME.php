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