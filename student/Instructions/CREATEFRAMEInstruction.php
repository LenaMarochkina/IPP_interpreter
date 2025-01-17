<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class CREATEFRAMEInstruction extends AbstractInstruction
{
    /**
     * Execute CREATEFRAME instruction
     * Create a new temporary frame
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->temporaryFrame = new Frame(E_VARIABLE_FRAME::TF);
    }
}