<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use Override;

class MOVEInstruction implements InstructionInterface
{
    /**
     * Execute MOVE instruction
     * Moves the value of the second operand to the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws OperandTypeException If some operand has wrong type
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [$argumentVariable, $argumentValue] = [$instruction->getArgument(0), $instruction->getArgument(1)];

        if ($argumentVariable === null || $argumentValue === null) {
            throw new SemanticException("Invalid MOVE instruction");
        }

        $argumentValueType = $interpreter->getOperandFinalType($argumentValue);
        $argumentValueValue = Value::getTypedValueString($argumentValueType, $interpreter->getOperandTypedValue($argumentValue));

        $interpreter->getArgumentVariable($argumentVariable)->setDefined(true);
        $interpreter->getArgumentVariable($argumentVariable)->setType($argumentValueType);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentValueValue);
    }
}