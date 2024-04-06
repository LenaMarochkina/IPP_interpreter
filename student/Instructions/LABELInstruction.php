<?php

namespace IPP\Student\Instructions;

use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class LABELInstruction extends AbstractInstruction
{
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        // Ignored because labels are processed before the instructions
    }
}