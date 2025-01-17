<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class GETCHARInstruction extends AbstractInstruction
{
    /**
     * Execute GETCHAR instruction
     * Gets the character at the specified index from the string and stores it in the variable
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws StringOperationException If some string operation fails
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentString,
            $argumentIndex
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
            $instruction->getArgument(2),
        ];

        if ($argumentVariable === null || $argumentString === null || $argumentIndex === null) {
            throw new SemanticException("Invalid GETCHAR instruction");
        }

        $argumentStringValue = $interpreter->getOperandTypedValue($argumentString);
        $argumentIndexValue = $interpreter->getOperandTypedValue($argumentIndex);

        if (!$interpreter->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("GETCHAR instruction second operand must be of type string");
        }

        if (!$interpreter->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("GETCHAR instruction third operand must be of type int");
        }

        if (!is_string($argumentStringValue) || $argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentStringValue)) {
            throw new StringOperationException("GETCHAR instruction index out of bounds: $argumentIndexValue");
        }

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentStringValue[$argumentIndexValue]);

    }
}