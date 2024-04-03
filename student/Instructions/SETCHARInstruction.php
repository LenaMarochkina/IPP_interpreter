<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class SETCHARInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentIndex,
            $argumentString
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
            $instruction->getArgument(2),
        ];

        $argumentVariableValue = $interpreter->getOperandTypedValue($argumentVariable);
        $argumentIndexValue = $interpreter->getOperandTypedValue($argumentIndex);
        $argumentStringValue = $interpreter->getOperandTypedValue($argumentString);

        if (!$interpreter->isOperandTypeOf($argumentVariable, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("SETCHAR instruction first operand must be of type string");
        }

        if (!$interpreter->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("SETCHAR instruction second operand must be of type int");
        }

        if (!$interpreter->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("SETCHAR instruction third operand must be of type string");
        }

        if ($argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentVariableValue)) {
            throw new StringOperationException("SETCHAR instruction index out of bounds: $argumentIndexValue >= " . mb_strlen($argumentVariableValue));
        }

        if (mb_strlen($argumentStringValue) < 1) {
            throw new StringOperationException("SETCHAR instruction value length must be greater than 0");
        }

        $argumentVariableValue[$argumentIndexValue] = $argumentStringValue[0];

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentVariableValue);
    }
}