<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class ORInstruction implements InstructionInterface
{
    /**
     * Execute OR instruction
     * Makes a logical OR of two operands and stores the result in the first operand
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