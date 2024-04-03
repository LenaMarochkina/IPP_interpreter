<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class POPSInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        if ($interpreter->dataStack->isEmpty()) {
            throw new ValueException("Data stack is empty");
        }

        $value = $interpreter->dataStack->pop();
        $detectedType = Value::determineValueType($value);

        $interpreter->getArgumentVariable($argument)->setDefined(true);
        $interpreter->getArgumentVariable($argument)->setType($detectedType);
        $interpreter->getArgumentVariable($argument)->setValue(Value::getTypedValueString($detectedType, $value));
    }
}