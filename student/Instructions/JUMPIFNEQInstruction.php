<?php

namespace IPP\Student\Instructions;

use Exception;
use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class JUMPIFNEQInstruction extends AbstractInstruction
{
    /**
     * Execute JUMPIFNEQ instruction
     * Jumps to the specified label if the operands are not equal
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If the variable frame does not exist
     * @throws OperandTypeException If some operand has wrong type
     * @throws SemanticException If some semantic error occurs
     * @throws ValueException If some value is wrong
     * @throws VariableAccessException If some variable does not exist
     * @throws Exception If some error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $isFromStack = $instruction->getIsStackInstruction();

        [
            $argumentLabel,
            $argumentRightSymbol,
            $argumentLeftSymbol,
        ] = [
            $instruction->getArgument(0),
            !$isFromStack ? $instruction->getArgument(2) : $interpreter->dataStack->pop(),
            !$isFromStack ? $instruction->getArgument(1) : $interpreter->dataStack->pop(),
        ];

        if ($argumentLabel === null || $argumentLeftSymbol === null || $argumentRightSymbol === null) {
            throw new SemanticException("Invalid {$instruction->getName()->value} instruction");
        }

        $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

        if (!is_string($labelValue))
            throw new ValueException("{$instruction->getName()->value} instruction label must be a string");

        if (!array_key_exists($labelValue, $interpreter->labels))
            throw new SemanticException("Unknown label $labelValue");

        $leftSymbolValue = $interpreter->getOperandTypedValue($argumentLeftSymbol);
        $rightSymbolValue = $interpreter->getOperandTypedValue($argumentRightSymbol);
        $leftSymbolType = $interpreter->getOperandFinalType($argumentLeftSymbol);
        $rightSymbolType = $interpreter->getOperandFinalType($argumentRightSymbol);

        if (!($interpreter->isOperandSameType($argumentLeftSymbol, $argumentRightSymbol) || $leftSymbolType === E_ARGUMENT_TYPE::NIL || $rightSymbolType === E_ARGUMENT_TYPE::NIL)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction operands must be of the same type or one of them must be nil");
        }

        if ($leftSymbolValue !== $rightSymbolValue)
            $interpreter->instructionCounter = $interpreter->labels[$labelValue] - 1;
    }
}