<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;

class READInstruction extends AbstractInstruction
{
    /**
     * Execute READ instruction
     * Reads a value from the input and stores it in the argument variable
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If some operand has wrong type
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws VariableAccessException If some variable does not exist
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentType,
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
        ];

        if ($argumentVariable === null || $argumentType === null) {
            throw new SemanticException("Invalid READ instruction");
        }

        $input = match ($argumentType->getValue()->getValue()) {
            "int" => $interpreter->getInput()->readInt(),
            "string" => $interpreter->getInput()->readString(),
            "bool" => $interpreter->getInput()->readBool(),
            "float" => $interpreter->getInput()->readFloat(),
            default => throw new OperandTypeException("Invalid argument type for READ instruction"),
        };
        $detectedType = Value::determineValueType($input);

        $interpreter->getArgumentVariable($argumentVariable)->setType($input !== null ? $detectedType : E_ARGUMENT_TYPE::NIL);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($input !== null ? Value::getTypedValueString($detectedType, $input) : "");
    }
}