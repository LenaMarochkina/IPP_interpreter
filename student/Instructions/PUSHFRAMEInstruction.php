<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class PUSHFRAMEInstruction extends AbstractInstruction
{
    /**
     * Execute PUSHFRAME instruction
     * Creates a new frame and pushes it to the local frame stack
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If temporary frame does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {

        if ($interpreter->temporaryFrame === null) {
            throw new FrameAccessException("Temporary frame does not exist");
        }

        $interpreter->temporaryFrame->setFrame(E_VARIABLE_FRAME::LF);

        $interpreter->localFrameStack->push($interpreter->temporaryFrame);
        $interpreter->temporaryFrame = null;
    }
}