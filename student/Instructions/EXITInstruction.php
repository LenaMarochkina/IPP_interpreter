<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class EXITInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentSymbol,
        ] = [
            $instruction->getArgument(0),
        ];

        $argumentSymbolValue = $interpreter->getOperandTypedValue($argumentSymbol);

        if (!$interpreter->isOperandTypeOf($argumentSymbol, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("EXIT instruction operand must be of type int");
        }

        if ($argumentSymbolValue < 0 || $argumentSymbolValue > 9) {
            throw new OperandValueException("EXIT instruction operand must be in range 0-9");
        }

        exit($argumentSymbolValue);
    }
}