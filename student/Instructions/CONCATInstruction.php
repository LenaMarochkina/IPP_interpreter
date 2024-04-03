<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class CONCATInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentLeftString,
            $argumentRightString
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
            $instruction->getArgument(2),
        ];

        $argumentLeftStringValue = $interpreter->getOperandTypedValue($argumentLeftString);
        $argumentRightStringValue = $interpreter->getOperandTypedValue($argumentRightString);

        if (!$interpreter->isOperandTypeOf($argumentLeftString, E_ARGUMENT_TYPE::STRING) || !$interpreter->isOperandTypeOf($argumentRightString, E_ARGUMENT_TYPE::STRING)) {
            throw new OperandTypeException("CONCAT instruction operands must be of type string");
        }

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($argumentLeftStringValue . $argumentRightStringValue);
    }
}