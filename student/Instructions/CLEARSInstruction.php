<?php

namespace IPP\Student\Instructions;

use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class CLEARSInstruction extends AbstractInstruction
{
    /**
     * Execute CLEARS instruction
     * Clears the stack
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->dataStack->clear();
    }
}