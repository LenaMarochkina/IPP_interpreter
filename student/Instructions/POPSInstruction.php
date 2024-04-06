<?php

namespace IPP\Student\Instructions;

use Exception;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Value;

class POPSInstruction extends AbstractInstruction
{
    /**
     * Execute POPS instruction
     * Pops the value from the data stack and stores it to the argument variable
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws ValueException If some value is wrong
     * @throws SemanticException If some semantic error occurs
     * @throws VariableAccessException If some variable does not exist
     * @throws Exception If some stack error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        if ($argument === null) {
            throw new SemanticException("Invalid POPS instruction");
        }

        if ($interpreter->dataStack->isEmpty()) {
            throw new ValueException("Data stack is empty");
        }

        $argumentValue = $interpreter->dataStack->pop();
        $type = $argumentValue->getType();
        $value = $argumentValue->getTypedValue();

        $argumentVariable = $interpreter->getArgumentVariable($argument);

        $argumentVariable->setDefined(true);
        $argumentVariable->setType($type);
        $argumentVariable->setValue(Value::getTypedValueString($type, $value));
    }
}