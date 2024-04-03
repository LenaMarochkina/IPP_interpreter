<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class GETCHARInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentString,
            $argumentIndex
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
            $instruction->getArgument(2),
        ];

        $argumentStringValue = $interpreter->getOperandTypedValue($argumentString);
        $argumentIndexValue = $interpreter->getOperandTypedValue($argumentIndex);

        if (!$interpreter->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("GETCHAR instruction second operand must be of type string");
        }

        if (!$interpreter->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("GETCHAR instruction third operand must be of type int");
        }

        if ($argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentStringValue)) {
            throw new StringOperationException("GETCHAR instruction index out of bounds: $argumentIndexValue >= " . mb_strlen($argumentStringValue));
        }

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentStringValue[$argumentIndexValue]);

    }
}