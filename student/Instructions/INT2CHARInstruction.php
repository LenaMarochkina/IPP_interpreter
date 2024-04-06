<?php

namespace IPP\Student\Instructions;

use Exception;
use IPP\Student\Argument;
use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class INT2CHARInstruction extends AbstractInstruction
{
    /**
     * Execute INT2CHAR instruction
     * Converts an integer to a character and stores it in the variable
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws StringOperationException If the value is out of bounds
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     * @throws Exception If an error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $isFromStack = $instruction->getIsStackInstruction();

        [
            $argumentVariable,
            $argumentNumber,
        ] = [
            $instruction->getArgument(0),
            !$isFromStack ? $instruction->getArgument(1) : $interpreter->dataStack->pop(),
        ];

        if ((!$isFromStack && $argumentVariable === null) || $argumentNumber === null) {
            throw new SemanticException("Invalid {$instruction->getName()->value} instruction");
        }

        $argumentNumberValue = $interpreter->getOperandTypedValue($argumentNumber);

        if (!$interpreter->isOperandTypeOf($argumentNumber, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction operand must be of type int");
        }

        if (!is_int($argumentNumberValue)) {
            throw new ValueException("{$instruction->getName()->value} instruction operand must be a number");
        }

        $value = mb_chr($argumentNumberValue);

        if (!$value) {
            throw new StringOperationException("{$instruction->getName()->value} instruction value out of bounds: $argumentNumberValue");
        }

        if (!$isFromStack) {
            $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
            $interpreter->getArgumentVariable($argumentVariable)->setValue($value);
        } else {
            $interpreter->dataStack->push(new Argument($value, E_ARGUMENT_TYPE::STRING));
        }
    }
}