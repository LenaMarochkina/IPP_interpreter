<?php

namespace IPP\Student;

use IPP\Student\Exception\OperandTypeException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Instructions\ADDInstruction;
use IPP\Student\Instructions\ANDInstruction;
use IPP\Student\Instructions\CALLInstruction;
use IPP\Student\Instructions\CONCATInstruction;
use IPP\Student\Instructions\CREATEFRAMEInstruction;
use IPP\Student\Instructions\DEFVARInstruction;
use IPP\Student\Instructions\EQInstruction;
use IPP\Student\Instructions\EXITInstruction;
use IPP\Student\Instructions\GETCHARInstruction;
use IPP\Student\Instructions\GTInstruction;
use IPP\Student\Instructions\IDIVInstruction;
use IPP\Student\Instructions\InstructionInterface;
use IPP\Student\Instructions\INT2CHARInstruction;
use IPP\Student\Instructions\JUMPIFEQInstruction;
use IPP\Student\Instructions\JUMPIFNEQInstruction;
use IPP\Student\Instructions\JUMPInstruction;
use IPP\Student\Instructions\LABELInstruction;
use IPP\Student\Instructions\LTInstruction;
use IPP\Student\Instructions\MOVEInstruction;
use IPP\Student\Instructions\MULInstruction;
use IPP\Student\Instructions\NOTInstruction;
use IPP\Student\Instructions\ORInstruction;
use IPP\Student\Instructions\POPFRAMEInstruction;
use IPP\Student\Instructions\POPSInstruction;
use IPP\Student\Instructions\PUSHFRAMEInstruction;
use IPP\Student\Instructions\PUSHSInstruction;
use IPP\Student\Instructions\READInstruction;
use IPP\Student\Instructions\RETURNInstruction;
use IPP\Student\Instructions\SETCHARInstruction;
use IPP\Student\Instructions\STRI2INTInstruction;
use IPP\Student\Instructions\STRLENInstruction;
use IPP\Student\Instructions\SUBInstruction;
use IPP\Student\Instructions\TYPEInstruction;
use IPP\Student\Instructions\WRITEInstruction;

global $INSTRUCTIONS;

$INSTRUCTIONS = [
    new BuiltInInstruction(E_INSTRUCTION_NAME::MOVE, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new MOVEInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::CREATEFRAME, [], new CREATEFRAMEInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::PUSHFRAME, [], new PUSHFRAMEInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::POPFRAME, [], new POPFRAMEInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::DEFVAR, [
        [E_ARGUMENT_TYPE::VAR]
    ], new DEFVARInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::CALL, [
        [E_ARGUMENT_TYPE::LABEL]
    ], new CALLInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::RETURN, [], new RETURNInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::PUSHS, [
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]
    ], new PUSHSInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::POPS, [
        [E_ARGUMENT_TYPE::VAR]
    ], new POPSInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::ADD, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR],
    ], new ADDInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::SUB, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR]
    ], new SUBInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::MUL, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR]
    ], new MULInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::IDIV, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR]
    ], new IDIVInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::LT, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new LTInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::GT, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new GTInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::EQ, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new EQInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::AND, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]
    ], new ANDInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::OR, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]
    ], new ORInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::NOT, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]
    ], new NOTInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::INT2CHAR, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR]
    ], new INT2CHARInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::STRI2INT, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR]
    ], new STRI2INTInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::READ, [
        [E_ARGUMENT_TYPE::VAR], [E_ARGUMENT_TYPE::TYPE]
    ], new READInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::WRITE, [
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new WRITEInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::CONCAT, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::VAR]
    ], new CONCATInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::STRLEN, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::VAR]
    ], new STRLENInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::GETCHAR, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]
    ], new GETCHARInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::SETCHAR, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::VAR]
    ], new SETCHARInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::TYPE, [
        [E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new TYPEInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::LABEL, [
        [E_ARGUMENT_TYPE::LABEL]
    ], new LABELInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::JUMP, [
        [E_ARGUMENT_TYPE::LABEL]
    ], new JUMPInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::JUMPIFEQ, [
        [E_ARGUMENT_TYPE::LABEL],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new JUMPIFEQInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::JUMPIFNEQ, [
        [E_ARGUMENT_TYPE::LABEL],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR],
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ], new JUMPIFNEQInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::EXIT, [
        [E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::VAR]
    ], new EXITInstruction()),
    new BuiltInInstruction(E_INSTRUCTION_NAME::DPRINT, [
        [E_ARGUMENT_TYPE::STRING, E_ARGUMENT_TYPE::INT, E_ARGUMENT_TYPE::BOOL, E_ARGUMENT_TYPE::NIL, E_ARGUMENT_TYPE::VAR]
    ]),
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

    /** @var InstructionInterface|null instruction to execute */
    private ?InstructionInterface $instruction;

    /**
     * @param E_INSTRUCTION_NAME $name name of the instruction
     * @param E_ARGUMENT_TYPE[][] $args types of arguments
     */
    public function __construct(E_INSTRUCTION_NAME $name, array $args, ?InstructionInterface $instruction = null)
    {
        $this->name = $name;
        $this->args = $args;
        $this->instruction = $instruction;
    }

    /**
     * Get instruction by name.
     *
     * @throws SemanticException if instruction not found
     */
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
                throw new OperandTypeException("Wrong type of argument {$arg->getStringValue()} at $index position for instruction '{$this->name->value}'");
            }
        }
    }

    /**
     * Get instruction to execute.
     *
     * @return InstructionInterface|null instruction to execute
     */
    public function getExecutionInstruction(): ?InstructionInterface
    {
        return $this->instruction;
    }
}
