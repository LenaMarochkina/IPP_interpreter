<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class STRI2INTInstruction implements InstructionInterface
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
            throw new OperandTypeException("STRI2INT instruction first operand must be of type string");
        }

        if (!$interpreter->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("STRI2INT instruction second operand must be of type int");
        }

        if ($argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentStringValue)) {
            throw new StringOperationException("STRI2INT instruction index out of bounds: $argumentIndexValue >= " . mb_strlen($argumentStringValue));
        }

        $charCode = mb_ord($argumentStringValue[$argumentIndexValue]);

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::INT);
        $interpreter->getArgumentVariable($argumentVariable)->setValue(strval($charCode));
    }
}