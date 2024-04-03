<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class PUSHFRAMEInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {

        if ($interpreter->temporaryFrame === null) {
            throw new FrameAccessException("Temporary frame does not exist");
        }

        $interpreter->localFrameStack->push($interpreter->temporaryFrame);
        $interpreter->temporaryFrame = null;
    }
}