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

class INT2FLOATInstruction implements InstructionInterface
{
    /**
     * Execute INT2FLOAT instruction
     * Converts int to float and stores the result in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentInteger,
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
        ];

        if ($argumentVariable === null || $argumentInteger === null) {
            throw new SemanticException("Invalid INT2FLOAT instruction arguments");
        }

        $argumentIntegerValue = $interpreter->getOperandTypedValue($argumentInteger);

        if (!$interpreter->isOperandTypeOf($argumentInteger, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("INT2FLOAT instruction second operand must be of type int");
        }

        $resultStringValue = sprintf('%f', $argumentIntegerValue);

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::FLOAT);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($resultStringValue);
    }
}