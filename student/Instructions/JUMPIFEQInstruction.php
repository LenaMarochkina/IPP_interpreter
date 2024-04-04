<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use Override;

class JUMPIFEQInstruction implements InstructionInterface
{
    /**
     * Execute JUMPIFEQ instruction
     * Jumps to the specified label if the two operands are equal
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws OperandTypeException If some operand has wrong type
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     */
    #[Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
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

        if ($argumentLabel === null || $argumentLeftSymbol === null || $argumentRightSymbol === null) {
            throw new SemanticException("Invalid JUMPIFEQ instruction");
        }

        $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

        if (!is_string($labelValue))
            throw new ValueException("JUMPIFEQ instruction label must be a string");

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