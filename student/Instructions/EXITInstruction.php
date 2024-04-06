<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class EXITInstruction extends AbstractInstruction
{
    /**
     * Execute EXIT instruction
     * Exits the program with the specified exit code
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If the variable frame does not exist
     * @throws OperandTypeException If some operand has wrong type
     * @throws OperandValueException If some operand has wrong value
     * @throws ValueException If some value is wrong
     * @throws SemanticException If some semantic error occurs
     * @throws VariableAccessException If some variable does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentSymbol,
        ] = [
            $instruction->getArgument(0),
        ];

        if ($argumentSymbol === null) {
            throw new SemanticException("Invalid EXIT instruction");
        }

        $argumentSymbolValue = $interpreter->getOperandTypedValue($argumentSymbol);

        if (!$interpreter->isOperandTypeOf($argumentSymbol, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("EXIT instruction operand must be of type int");
        }

        if ($argumentSymbolValue < 0 || $argumentSymbolValue > 9) {
            throw new OperandValueException("EXIT instruction operand must be in range 0-9");
        }

        exit($argumentSymbolValue);
    }
}