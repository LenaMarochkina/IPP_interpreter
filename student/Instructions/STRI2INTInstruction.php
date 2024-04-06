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
use IPP\Student\Value;

class STRI2INTInstruction extends AbstractInstruction
{
    /**
     * Execute STRI2INT instruction
     * Gets the ASCII code of the character at the specified index in the first operand and stores it in the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws StringOperationException If some string operation is wrong
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     * @throws Exception If some error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $isFromStack = $instruction->getIsStackInstruction();

        [
            $argumentVariable,
            $argumentIndex,
            $argumentString,
        ] = [
            $instruction->getArgument(0),
            !$isFromStack ? $instruction->getArgument(2) : $interpreter->dataStack->pop(),
            !$isFromStack ? $instruction->getArgument(1) : $interpreter->dataStack->pop(),
        ];

        if ((!$isFromStack && $argumentVariable === null) || $argumentString === null || $argumentIndex === null) {
            throw new SemanticException("Invalid {$instruction->getName()->value} instruction");
        }

        $argumentStringValue = $interpreter->getOperandTypedValue($argumentString);
        $argumentIndexValue = $interpreter->getOperandTypedValue($argumentIndex);

        if (!$interpreter->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction first operand must be of type string");
        }

        if (!$interpreter->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction second operand must be of type int");
        }

        if (!is_string($argumentStringValue) || $argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentStringValue)) {
            throw new StringOperationException("{$instruction->getName()->value} instruction index out of bounds: $argumentIndexValue");
        }

        $charCode = mb_ord($argumentStringValue[$argumentIndexValue]);

        if (!$isFromStack) {
            $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::INT);
            $interpreter->getArgumentVariable($argumentVariable)->setValue(strval($charCode));
        } else {
            $interpreter->dataStack->push(new Argument(
                Value::getTypedValueString(E_ARGUMENT_TYPE::INT, $charCode),
                E_ARGUMENT_TYPE::INT
            ));
        }
    }
}