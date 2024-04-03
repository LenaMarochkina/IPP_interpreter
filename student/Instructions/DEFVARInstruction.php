<?php

namespace IPP\Student\Instructions;

use IPP\Student\Instruction;
use IPP\Student\Interpreter;
use IPP\Student\Variable;

class DEFVARInstruction implements InstructionInterface
{
    #[\Override] public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $argument = $instruction->getArgument(0);

        [$variableFrame, $variableName] = Variable::parseVariableName($argument->getStringValue());

        $interpreter->getVariableFrame($variableFrame)->createVariable($variableName, $argument->getType());
    }
}