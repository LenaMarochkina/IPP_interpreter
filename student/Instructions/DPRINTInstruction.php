<?php

namespace IPP\Student\Instructions;

use IPP\Core\Exception\OutputFileException;
use IPP\Student\E_ARGUMENT_TYPE;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\VariableAccessException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class DPRINTInstruction extends AbstractInstruction
{
    /**
     * Execute DPRINT instruction
     * Prints the value of the first operand
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If some variable frame does not exist
     * @throws SemanticException If some semantic error occurs
     * @throws VariableAccessException If some variable does not exist
     * @throws OutputFileException If some output file error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        $type = $interpreter->getOperandFinalType($argument);

        $isVariable = $argument->getType() === E_ARGUMENT_TYPE::VAR;

        if ($isVariable)
            [$frame, $name] = Variable::parseVariableName($argument->getValue()->getValue());

        $interpreter->debugger->printSymbolRow(
            $isVariable ? $name : '-',
            $isVariable ? $frame->value : '-',
            $type->value,
            $isVariable ? $interpreter->getArgumentVariable($argument)->getValue() : $argument->getValue(),
        );
        $interpreter->getStdErr()->writeString("\n");
    }
}