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
use Override;

class SETCHARInstruction implements InstructionInterface
{
    /**
     * Execute SETCHAR instruction
     * Sets the character at the specified index in the first operand to the first character of the third operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws StringOperationException If some string operation is wrong
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentIndex,
            $argumentString
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
            $instruction->getArgument(2),
        ];

        if ($argumentVariable === null || $argumentIndex === null || $argumentString === null) {
            throw new SemanticException("Invalid SETCHAR instruction");
        }

        $argumentVariableValue = $interpreter->getOperandTypedValue($argumentVariable);
        $argumentIndexValue = $interpreter->getOperandTypedValue($argumentIndex);
        $argumentStringValue = $interpreter->getOperandTypedValue($argumentString);

        if (!$interpreter->isOperandTypeOf($argumentVariable, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("SETCHAR instruction first operand must be of type string");
        }

        if (!$interpreter->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("SETCHAR instruction second operand must be of type int");
        }

        if (!$interpreter->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("SETCHAR instruction third operand must be of type string");
        }

        if (!is_string($argumentVariableValue) || $argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentVariableValue)) {
            throw new StringOperationException("SETCHAR instruction index out of bounds: $argumentIndexValue");
        }

        if (!is_string($argumentStringValue) || mb_strlen($argumentStringValue) < 1) {
            throw new StringOperationException("SETCHAR instruction value length must be greater than 0");
        }

        if (!is_int($argumentIndexValue)) {
            throw new StringOperationException("SETCHAR instruction index must be an integer");
        }

        $argumentVariableValue[$argumentIndexValue] = $argumentStringValue[0];

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentVariableValue);
    }
}