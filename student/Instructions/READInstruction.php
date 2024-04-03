<?php

namespace IPP\Student\Instructions;

use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;
use IPP\Student\Variable;

class READInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        [
            $argumentVariable,
            $argumentType,
        ] = [
            $instruction->getArgument(0),
            $instruction->getArgument(1),
        ];

        $input = match ($argumentType->getValue()->getValue()) {
            "int" => $interpreter->getInput()->readInt(),
            "string" => $interpreter->getInput()->readString(),
            "bool" => $interpreter->getInput()->readBool(),
            default => throw new OperandTypeException("Invalid argument type for READ instruction"),
        };
        $detectedType = Value::determineValueType($input);

        $interpreter->getArgumentVariable($argumentVariable)->setType($input !== null ? $detectedType : E_ARGUMENT_TYPE::NIL);
        $interpreter->getArgumentVariable($argumentVariable)->setValue($input !== null ? Value::getTypedValueString($detectedType, $input) : "");
    }
}