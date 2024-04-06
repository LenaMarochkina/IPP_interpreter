<?php

namespace IPP\Student\Instructions;

use Exception;
use IPP\Student\Exception\ValueException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class RETURNInstruction extends AbstractInstruction
{
    /**
     * Execute RETURN instruction
     * Pops the top frame from the call stack and sets the instruction counter to the value popped from the call stack
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws ValueException If call stack is empty
     * @throws Exception If instruction counter is not set
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        if ($interpreter->callStack->isEmpty()) {
            throw new ValueException("Call stack is empty");
        }

        $returnFrame = $interpreter->callStack->pop();

        if (!is_int($returnFrame)) {
            throw new Exception("Instruction counter is not set");
        }

        $interpreter->instructionCounter = $returnFrame;
    }
}