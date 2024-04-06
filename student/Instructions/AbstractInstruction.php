<?php

namespace IPP\Student\Instructions;

use IPP\Student\Instruction;
use IPP\Student\Interpreter;

abstract class AbstractInstruction
{
    protected bool $isStackInstruction;

    public function __construct(?bool $isStackInstruction = null)
    {
        $this->isStackInstruction = $isStackInstruction ?? false;
    }

    public function getIsStackInstruction(): bool
    {
        return $this->isStackInstruction;
    }

    public abstract function execute(Interpreter $interpreter, Instruction $instruction): void;
}