<?php

namespace IPP\Student\Instructions;

use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class LABELInstruction implements InstructionInterface
{
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        // Ignored because labels are processed before the instructions
    }
}