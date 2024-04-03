<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\E_INSTRUCTION_NAME;
use IPP\Student\E_VARIABLE_FRAME;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Frame;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class JUMPIFEQInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentLabel,
            $argumentLeftSymbol,
            $argumentRightSymbol,
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
            $instruction->getArgument(2),
        ];

        $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

        if (!array_key_exists($labelValue, $interpreter->labels))
            throw new SemanticException("Unknown label $labelValue");

        $leftSymbolValue = $interpreter->getOperandTypedValue($argumentLeftSymbol);
        $rightSymbolValue = $interpreter->getOperandTypedValue($argumentRightSymbol);
        $leftSymbolType = $interpreter->getOperandFinalType($argumentLeftSymbol);
        $rightSymbolType = $interpreter->getOperandFinalType($argumentRightSymbol);

        if (!($interpreter->isOperandSameType($argumentLeftSymbol, $argumentRightSymbol) || $leftSymbolType === E_ARGUMENT_TYPE::NIL || $rightSymbolType === E_ARGUMENT_TYPE::NIL)) {
            throw new OperandTypeException("JUMPIFEQ instruction operands must be of the same type or one of them must be nil");
        }

        if ($leftSymbolValue === $rightSymbolValue)
            $interpreter->instructionCounter = $interpreter->labels[$labelValue] - 1;
    }
}