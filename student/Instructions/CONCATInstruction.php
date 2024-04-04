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
use Override;

class CONCATInstruction implements InstructionInterface
{
    /**
     * Execute CONCAT instruction
     * Concatenates two strings and stores the result in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws OperandTypeException If some operand has wrong type
     * @throws ValueException If some value is wrong
     * @throws SemanticException If some semantic error occurs
     * @throws VariableAccessException If some variable does not exist
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentLeftString,
            $argumentRightString
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
            $instruction->getArgument(2),
        ];

        if ($argumentVariable === null || $argumentLeftString === null || $argumentRightString === null) {
            throw new SemanticException("Invalid CONCAT instruction");
        }

        $argumentLeftStringValue = $interpreter->getOperandTypedValue($argumentLeftString);
        $argumentRightStringValue = $interpreter->getOperandTypedValue($argumentRightString);

        if (!$interpreter->isOperandTypeOf($argumentLeftString, E_ARGUMENT_TYPE::STRING) || !$interpreter->isOperandTypeOf($argumentRightString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("CONCAT instruction operands must be of type string");
        }

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentLeftStringValue . $argumentRightStringValue);
    }
}