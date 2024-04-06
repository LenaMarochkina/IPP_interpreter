<?php

namespace IPP\Student\Instructions;

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
use IPP\Student\Value;
use PhpParser\Node\Arg;

class INT2FLOATInstruction extends AbstractInstruction
{
    /**
     * Execute INT2FLOAT instruction
     * Converts int to float and stores the result in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $isFromStack = $instruction->getIsStackInstruction();

        [
            $argumentVariable,
            $argumentInteger,
        ] = [
            $instruction->getArgument(0),
            !$isFromStack ? $instruction->getArgument(1) : $interpreter->dataStack->pop(),
        ];

        if ((!$isFromStack && $argumentVariable === null) || $argumentInteger === null) {
            throw new SemanticException("Invalid {$instruction->getName()->value} instruction arguments");
        }

        $argumentIntegerValue = $interpreter->getOperandTypedValue($argumentInteger);

        if (!$interpreter->isOperandTypeOf($argumentInteger, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction second operand must be of type int");
        }

        $resultStringValue = sprintf('%f', $argumentIntegerValue);

        if (!$isFromStack) {
            $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::FLOAT);
            $interpreter->getArgumentVariable($argumentVariable)->setValue($resultStringValue);
        } else {
            $interpreter->dataStack->push(new Argument(
                Value::getTypedValueString(E_ARGUMENT_TYPE::FLOAT, $resultStringValue),
                E_ARGUMENT_TYPE::FLOAT
            ));
        }
    }
}