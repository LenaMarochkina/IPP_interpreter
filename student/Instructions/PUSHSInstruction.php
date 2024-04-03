<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class PUSHSInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        $argumentValue = $interpreter->getOperandTypedValue($argument);

        $interpreter->dataStack->push($argumentValue);
    }
}