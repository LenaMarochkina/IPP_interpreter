<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class PUSHFRAMEInstruction implements InstructionInterface
{
    /**
     * Execute PUSHFRAME instruction
     * Creates a new frame and pushes it to the local frame stack
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If temporary frame does not exist
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {

        if ($interpreter->temporaryFrame === null) {
            throw new FrameAccessException("Temporary frame does not exist");
        }

        $interpreter->localFrameStack->push($interpreter->temporaryFrame);
        $interpreter->temporaryFrame = null;
    }
}