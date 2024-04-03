<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class CALLInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentLabel,
        ] = [
            $instruction->getArgument(0),
        ];

        $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

        if (!array_key_exists($labelValue, $interpreter->labels))
            throw new SemanticException("Unknown label $labelValue");

        $interpreter->callStack->push($interpreter->instructionCounter);

        $interpreter->instructionCounter = $interpreter->labels[$labelValue] - 1;
    }
}