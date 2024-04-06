<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class CALLInstruction extends AbstractInstruction
{
    /**
     * Execute CALL instruction
     * Jump to the label and save the current instruction counter to the call stack
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws SemanticException If some semantic error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentLabel,
        ] = [
            $instruction->getArgument(0),
        ];

        if ($argumentLabel === null)
            throw new SemanticException("Invalid CALL instruction");

        $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

        if (!is_string($labelValue))
            throw new SemanticException("Invalid label value");

        if (!array_key_exists($labelValue, $interpreter->labels))
            throw new SemanticException("Unknown label $labelValue");

        $interpreter->callStack->push($interpreter->instructionCounter);

        $interpreter->instructionCounter = $interpreter->labels[$labelValue] - 1;
    }
}