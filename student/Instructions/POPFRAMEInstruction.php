<?php

namespace IPP\Student\Instructions;

use Exception;
use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class POPFRAMEInstruction extends AbstractInstruction
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
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        if ($interpreter->localFrameStack->isEmpty()) {
            throw new FrameAccessException("Local frame does not exist");
        }

        $frame = $interpreter->localFrameStack->pop();

        if (is_null($frame)) {
            throw new Exception("Error while popping frame from local frame stack");
        }

        $frame->setFrame(E_VARIABLE_FRAME::TF);

        $interpreter->temporaryFrame = $frame;
    }
}