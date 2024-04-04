<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class ANDInstruction implements InstructionInterface
{
    /**
     * Execute AND instruction
     * Updates the first operand with the result of logical AND operation on both operands
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->runBool($instruction);
    }
}