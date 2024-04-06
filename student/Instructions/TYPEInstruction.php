<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class TYPEInstruction extends AbstractInstruction
{
    /**
     * Execute TYPE instruction
     * Gets the type of the symbol and stores it in the variable
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws VariableAccessException If some variable does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentSymbol,
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
        ];

        if ($argumentVariable === null || $argumentSymbol === null) {
            throw new SemanticException("Invalid TYPE instruction");
        }

        $symbolType = $interpreter->isOperandDefined($argumentSymbol) ? $interpreter->getOperandFinalType($argumentSymbol)->value : "";

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($symbolType);
    }
}