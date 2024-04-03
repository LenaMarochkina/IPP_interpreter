<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class CREATEFRAMEInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->temporaryFrame = new Frame(E_VARIABLE_FRAME::TF);
    }
}