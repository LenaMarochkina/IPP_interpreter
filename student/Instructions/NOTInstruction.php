<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class NOTInstruction implements InstructionInterface
{
    /**
     * Execute NOT instruction
     * Negates the value of the first operand and stores the result in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->runBool($instruction);
    }
}