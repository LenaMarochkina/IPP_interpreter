<?php

namespace IPP\Student\Instructions;

use IPP\Student\Argument;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;

class PUSHSInstruction extends AbstractInstruction
{
    /**
     * Execute PUSHS instruction
     * Pushes the value of the argument to the data stack
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws OperandTypeException If some operand has wrong type
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        if (is_null($argument)) {
            throw new SemanticException("Invalid POPS instruction");
        }

        $argumentType = $interpreter->getOperandFinalType($argument);
        $argumentValue = $interpreter->getOperandTypedValue($argument);

        $interpreter->dataStack->push(new Argument(
            Value::getTypedValueString($argumentType, $argumentValue),
            $argumentType
        ));
    }
}