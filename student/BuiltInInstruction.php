<?php

namespace IPP\Student;

use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;

global $INSTRUCTIONS;

$INSTRUCTIONS = [
    new BuiltInInstruction(E_INSTRUCTION_NAME::MOVE, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::CREATEFRAME, []),
    new BuiltInInstruction(E_INSTRUCTION_NAME::PUSHFRAME, []),
    new BuiltInInstruction(E_INSTRUCTION_NAME::POPFRAME, []),
    new BuiltInInstruction(E_INSTRUCTION_NAME::DEFVAR, [[E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::CALL, [[E_ARGUMENT_TYPE::LABEL]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::RETURN, []),
    new BuiltInInstruction(E_INSTRUCTION_NAME::PUSHS, [[E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::POPS, [[E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::ADD, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR],
    ]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::SUB, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]
    ]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::MUL, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::IDIV, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::LT, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::GT, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::EQ, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::AND, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::OR, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::NOT, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::INT2CHAR, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::STRI2INT, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::READ, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::TYPE]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::WRITE, [[E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::CONCAT, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::STRLEN, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::GETCHAR, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::SETCHAR, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::TYPE, [[E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::LABEL, [[E_ARGUMENT_TYPE::LABEL]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::JUMP, [[E_ARGUMENT_TYPE::LABEL]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::JUMPIFEQ, [[E_ARGUMENT_TYPE::LABEL], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::JUMPIFNEQ, [[E_ARGUMENT_TYPE::LABEL], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::EXIT, [[E_ARGUMENT_TYPE::INT]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::DPRINT, [[E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]]),
    new BuiltInInstruction(E_INSTRUCTION_NAME::BREAK, []),
];

class BuiltInInstruction
{
    /** @var E_INSTRUCTION_NAME name of the instruction */
    private E_INSTRUCTION_NAME $name;

    /**
     * Types of arguments:
     * 1st dimension - argument of the instruction (e.g. 1st argument, 2nd argument)
     * 2nd dimension - possible types of the argument (e.g. int|string|bool)
     *
     * @var E_ARGUMENT_TYPE[][] types of arguments
     */
    private array $args;

    /**
     * @param E_INSTRUCTION_NAME $name name of the instruction
     * @param E_ARGUMENT_TYPE[][] $args types of arguments
     */
    public function __construct(E_INSTRUCTION_NAME $name, array $args)
    {
        $this->name = $name;
        $this->args = $args;
    }

    public static function getInstruction(E_INSTRUCTION_NAME $name): BuiltInInstruction
    {
        global $INSTRUCTIONS;
        foreach ($INSTRUCTIONS as $instruction) {
            if ($instruction->getName() === $name) {
                return $instruction;
            }
        }

        throw new SemanticException("Instruction '$name->value' not found");
    }

    /**
     * Get name of the instruction.
     *
     * @return E_INSTRUCTION_NAME name of the instruction
     */
    public function getName(): E_INSTRUCTION_NAME
    {
        return $this->name;
    }

    /**
     * Get types of arguments for the instruction.
     *
     * @return E_ARGUMENT_TYPE[][] possible types of arguments
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Get arguments count of the instruction.
     *
     * @return int count of arguments
     */
    public function getArgsCount(): int
    {
        return count($this->args);
    }

    /**
     * Get argument possible types at index.
     *
     * @param int $index index of the argument
     *
     * @return E_ARGUMENT_TYPE[] possible types of the argument
     */
    public function getArgTypes(int $index): array
    {
        return $this->args[$index];
    }

    /**
     * Validates arguments of the instruction.
     *
     * @param Argument[] $args arguments to validate
     *
     * @throws SemanticException    if wrong number of arguments
     * @throws OperandTypeException if wrong type of some argument
     */
    public function validateArgs(array $args): void
    {
        if (count($args) !== count($this->args)) {
            throw new SemanticException("Wrong number of arguments for instruction '{$this->name->value}'");
        }

        foreach ($args as $index => $arg) {
            if (!in_array($arg->getType(), $this->args[$index])) {
                throw new OperandTypeException("Wrong type of argument {$arg->getStringValue()} at {$index} position for instruction '{$this->name->value}'");
            }
        }
    }
}
