<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class LTInstruction implements InstructionInterface
{
    /**
     * Execute LT instruction
     * Compares two operands and stores the result in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws SemanticException If some semantic error occurs
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->runRelational($instruction);
    }
}