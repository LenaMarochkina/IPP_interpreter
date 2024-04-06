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

class STRLENInstruction extends AbstractInstruction
{
    /**
     * Execute STRLEN instruction
     * Gets the length of the string and stores it in the variable
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
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
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
        ];

        if ($argumentVariable === null || $argumentString === null) {
            throw new SemanticException("Invalid STRLEN instruction");
        }

        $argumentStringValue = $interpreter->getOperandTypedValue($argumentString);

        if (!$interpreter->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("STRLEN instruction operand must be of type string");
        }

        if (!is_string($argumentStringValue)) {
            throw new ValueException("STRLEN instruction operand must be of type string");
        }

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::INT);
        $interpreter->getArgumentVariable($argumentVariable)->setValue(strval(mb_strlen($argumentStringValue)));
    }
}