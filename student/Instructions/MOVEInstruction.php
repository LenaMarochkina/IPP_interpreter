<?php

namespace IPP\Student\Instructions;

use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class MOVEInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [$argumentVariable, $argumentValue] = [$instruction->getArgument(0), $instruction->getArgument(1)];

        $argumentValueType = $interpreter->getOperandFinalType($argumentValue);
        $argumentValueValue = Value::getTypedValueString($argumentValueType, $interpreter->getOperandTypedValue($argumentValue));

        $interpreter->getArgumentVariable($argumentVariable)->setDefined(true);
        $interpreter->getArgumentVariable($argumentVariable)->setType($argumentValueType);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentValueValue);
    }
}