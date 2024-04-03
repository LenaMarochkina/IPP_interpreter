<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class POPFRAMEInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        if ($interpreter->localFrameStack->isEmpty()) {
            throw new FrameAccessException("Local frame does not exist");
        }

        $interpreter->temporaryFrame = $interpreter->localFrameStack->pop();
    }
}