<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class DEFVARInstruction extends AbstractInstruction
{
    /**
     * Execute DEFVAR instruction
     * Creates a new variable in the specified variable frame
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If the variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        if ($argument === null) {
            throw new SemanticException("Invalid DEFVAR instruction");
        }

        $argumentValue = $argument->getStringValue();

        if ($argumentValue === null) {
            throw new SemanticException("Invalid DEFVAR instruction");
        }

        [$variableFrame, $variableName] = Variable::parseVariableName($argumentValue);

        $frame = $interpreter->getVariableFrame($variableFrame);

        if ($frame === null) {
            throw new FrameAccessException("Variable frame $variableFrame->value does not exist");
        }

        $frame->createVariable($variableName, $argument->getType());
    }
}