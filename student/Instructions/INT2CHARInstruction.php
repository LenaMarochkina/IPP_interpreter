<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class INT2CHARInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [$argumentVariable, $argumentNumber] = [$instruction->getArgument(0), $instruction->getArgument(1)];

        $argumentNumberValue = $interpreter->getOperandTypedValue($argumentNumber);

        if (!$interpreter->isOperandTypeOf($argumentNumber, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("INT2CHAR instruction operand must be of type int");
        }

        $value = mb_chr($argumentNumberValue);

        if (!$value) {
            throw new StringOperationException("INT2CHAR instruction value out of bounds: $argumentNumberValue");
        }

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($value);
    }
}