<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class STRLENInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentString,
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
        ];

        $argumentStringValue = $interpreter->getOperandTypedValue($argumentString);

        if (!$interpreter->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("STRLEN instruction operand must be of type string");
        }

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::INT);
        $interpreter->getArgumentVariable($argumentVariable)->setValue(strval(mb_strlen($argumentStringValue)));
    }
}