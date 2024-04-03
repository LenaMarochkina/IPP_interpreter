<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class TYPEInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentSymbol,
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
        ];

        $symbolType = $interpreter->isOperandDefined($argumentSymbol) ? $interpreter->getOperandFinalType($argumentSymbol)->value : "";

        $interpreter->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($symbolType);
    }
}