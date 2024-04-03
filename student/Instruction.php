<?php

namespace IPP\Student;

use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;

class Instruction
{
    /**
     * @var E_INSTRUCTION_NAME instruction name
     */
    private E_INSTRUCTION_NAME $_name;

    /**
     * @var Argument[] array of instruction arguments
     */
    private array $_arguments;
    /**
     * @var int order of the instruction
     */
    private int $_order;

    /**
     * @param E_INSTRUCTION_NAME $name instruction name
     * @param Argument[] $arguments array of instruction arguments
     * @param int $order order of the instruction
     * @throws SemanticException
     * @throws OperandTypeException
     */
    public function __construct(E_INSTRUCTION_NAME $name, array $arguments, int $order)
    {
        $this->_name = $name;
        $this->_arguments = $arguments;
        $this->_order = $order;

        $builtInInstruction = BuiltInInstruction::getInstruction($name);
        $builtInInstruction->validateArgs($arguments);
    }

    /**
     * @return E_INSTRUCTION_NAME instruction name
     */
    public function getName(): E_INSTRUCTION_NAME
    {
        return $this->_name;
    }

    /**
     * Get instruction arguments
     *
     * @return Argument[] array of instruction arguments
     */
    public function getArguments(): array
    {
        return $this->_arguments;
    }

    /**
     * Get argument at index
     *
     * @param int $index index of the argument
     * @return Argument|null argument at index
     */
    public function getArgument(int $index): Argument|null
    {
        if (!isset($this->_arguments[$index])) {
            return null;
        }

        return $this->_arguments[$index];
    }

    /**
     * Get instruction order
     *
     * @return int order of the instruction
     */
    public function getOrder(): int
    {
        return $this->_order;
    }
}