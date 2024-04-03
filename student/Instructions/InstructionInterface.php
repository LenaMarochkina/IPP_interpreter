<?php

namespace IPP\Student\Instructions;

use IPP\Student\Instruction;
use IPP\Student\Interpreter;

interface InstructionInterface
{
    public function execute(Interpreter $interpreter, Instruction $instruction): void;
}