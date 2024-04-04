<?php

namespace IPP\Student\Instructions;

use Exception;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class POPFRAMEInstruction implements InstructionInterface
{
    /**
     * Execute POPFRAME instruction
     * Pops the top frame from the local frame stack and makes it the temporary frame
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If local frame does not exist
     * @throws Exception If some error occurs
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        if ($interpreter->localFrameStack->isEmpty()) {
            throw new FrameAccessException("Local frame does not exist");
        }

        $interpreter->temporaryFrame = $interpreter->localFrameStack->pop();
    }
}