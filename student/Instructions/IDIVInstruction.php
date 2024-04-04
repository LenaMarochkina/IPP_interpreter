<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class IDIVInstruction implements InstructionInterface
{
    /**
     * Execute IDIV instruction
     * Divides two operands and stores the result in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws OperandTypeException If some operand has wrong type
     * @throws OperandValueException If some operand has wrong value
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->runMath($instruction);
    }
}