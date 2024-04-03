<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class WRITEInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);
        switch ($argument->getType()) {
            case E_ARGUMENT_TYPE::INT:
            case E_ARGUMENT_TYPE::STRING:
            case E_ARGUMENT_TYPE::BOOL:
            case E_ARGUMENT_TYPE::NIL:
                $interpreter->runOutput($argument->getType(), $argument->getTypedValue());

                break;
            case E_ARGUMENT_TYPE::VAR:
                if (!$interpreter->isOperandDefined($argument)) {
                    throw new ValueException("Variable {$argument->getStringValue()} is not defined");
                }

                $interpreter->runOutput(
                    $interpreter->getArgumentVariable($argument)->getType(),
                    $interpreter->getArgumentVariable($argument)->getTypedValue(),
                );

                break;
            default:
                throw new OperandTypeException("Invalid argument type for WRITE instruction");
        }
    }
}