<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class JUMPInstruction extends AbstractInstruction
{
    /**
     * Execute JUMP instruction
     * Jumps to the label
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentLabel,
        ] = [
            $instruction->getArgument(0),
        ];

        if ($argumentLabel === null)
            throw new SemanticException("Invalid JUMP instruction");

        $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

        if (!is_string($labelValue))
            throw new ValueException("JUMP instruction label must be a string");

        if (!array_key_exists($labelValue, $interpreter->labels))
            throw new SemanticException("Unknown label $labelValue");

        $interpreter->instructionCounter = $interpreter->labels[$labelValue] - 1;
    }
}