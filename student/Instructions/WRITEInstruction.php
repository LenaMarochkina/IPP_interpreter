<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class WRITEInstruction extends AbstractInstruction
{
    /**
     * Execute WRITE instruction
     * Writes the argument to the output
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws ValueException If some value is wrong
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws VariableAccessException If some variable does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        if ($argument === null) {
            throw new SemanticException("Invalid WRITE instruction");
        }

        switch ($argument->getType()) {
            case E_ARGUMENT_TYPE::INT:
            case E_ARGUMENT_TYPE::STRING:
            case E_ARGUMENT_TYPE::FLOAT:
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