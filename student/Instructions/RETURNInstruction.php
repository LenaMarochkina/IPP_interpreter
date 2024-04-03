<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class RETURNInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        if ($interpreter->callStack->isEmpty()) {
            throw new ValueException("Call stack is empty");
        }

        $interpreter->instructionCounter = $interpreter->callStack->pop();
    }
}