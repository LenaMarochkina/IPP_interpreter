<?php

namespace IPP\Student\Instructions;

use Exception;
use IPP\Student\Argument;
use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;

class FLOAT2INTInstruction extends AbstractInstruction
{
    /**
     * Execute FLOAT2INT instruction
     * Converts float to int and stores the result in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     * @throws Exception If some other error occurs
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

        if (!$interpreter->isOperandTypeOf($argumentInteger, E_ARGUMENT_TYPE::FLOAT)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction second operand must be of type float");
        }

        if (!$isFromStack) {
            $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::INT);
            $interpreter->getArgumentVariable($argumentVariable)->setValue(Value::getTypedValueString(E_ARGUMENT_TYPE::INT, $argumentIntegerValue));
        } else {
            $interpreter->dataStack->push(new Argument(
                Value::getTypedValueString(E_ARGUMENT_TYPE::INT, $argumentIntegerValue),
                E_ARGUMENT_TYPE::INT
            ));
        }
    }
}