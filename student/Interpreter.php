<?php

namespace IPP\Student;

use DivisionByZeroError;
use Exception;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\XMLException;
use IPP\Core\Interface\InputReader;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;

use DOMElement;
use DOMAttr;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;

global $MATH_MAP;
/**
 * Math map
 *
 * @var array{E_INSTRUCTION_NAME: callable(int, int): int} $MATH_MAP
 */
$MATH_MAP = [
    E_INSTRUCTION_NAME::ADD->value => fn(int $a, int $b) => $a + $b,
    E_INSTRUCTION_NAME::SUB->value => fn(int $a, int $b) => $a - $b,
    E_INSTRUCTION_NAME::MUL->value => fn(int $a, int $b) => $a * $b,
    E_INSTRUCTION_NAME::IDIV->value => fn(int $a, int $b) => intval($a / $b),
];
global $RELATIONAL_MAP;

/**
 * Relational map
 *
 * @var array{E_INSTRUCTION_NAME: callable(mixed, mixed): bool} $RELATIONAL_MAP
 */
$RELATIONAL_MAP = [
    E_INSTRUCTION_NAME::LT->value => fn(mixed $a, mixed $b) => $a < $b,
    E_INSTRUCTION_NAME::GT->value => fn(mixed $a, mixed $b) => $a > $b,
    E_INSTRUCTION_NAME::EQ->value => fn(mixed $a, mixed $b) => $a == $b,
];

global $BOOL_MAP;

/**
 * Bool map
 *
 * @var array{E_INSTRUCTION_NAME: callable(bool, bool): bool} $BOOL_MAP
 */
$BOOL_MAP = [
    E_INSTRUCTION_NAME::AND->value => fn(bool $a, bool $b) => $a && $b,
    E_INSTRUCTION_NAME::OR->value => fn(bool $a, bool $b) => $a || $b,
    E_INSTRUCTION_NAME::NOT->value => fn(bool $a) => !$a,
];

class Interpreter extends AbstractInterpreter
{
    public Frame $globalFrame;
    public Frame|null $temporaryFrame;

    /**
     * @var GenericStack<Frame> $localFrameStack
     */
    public GenericStack $localFrameStack;

    /**
     * @var array{string: int} $labels Labels
     */
    public array $labels = [];

    /**
     * @var GenericStack<int> $callStack Call stack
     */
    public GenericStack $callStack;

    /**
     * @var GenericStack<int|string|bool|null> $dataStack Data stack
     */
    public GenericStack $dataStack;

    public int $instructionCounter = 0;

    protected function init(): void
    {
        parent::init();

        $this->globalFrame = new Frame(E_VARIABLE_FRAME::GF);
        $this->localFrameStack = new GenericStack();
        $this->temporaryFrame = null;

        $this->callStack = new GenericStack();
        $this->dataStack = new GenericStack();
    }

    /**
     * Validate variable frame
     * 1. Local frame stack must not be empty
     * 2. Temporary frame must exist
     *
     * @param E_VARIABLE_FRAME $frame Frame type
     * @throws FrameAccessException If frame is not valid
     */
    public function validateVariableFrame(E_VARIABLE_FRAME $frame): void
    {
        if ($this->localFrameStack->isEmpty() && $frame == E_VARIABLE_FRAME::LF) {
            throw new FrameAccessException("Local frame does not exist");
        }

        if ($this->temporaryFrame === null && $frame == E_VARIABLE_FRAME::TF) {
            throw new FrameAccessException("Temporary frame does not exist");
        }
    }

    /**
     * Get variable frame
     *
     * @param E_VARIABLE_FRAME $frame Frame type
     * @return Frame Frame instance
     * @throws FrameAccessException If frame is not valid
     */
    public function getVariableFrame(E_VARIABLE_FRAME $frame): Frame
    {
        $this->validateVariableFrame($frame);

        return match ($frame) {
            E_VARIABLE_FRAME::GF => $this->globalFrame,
            E_VARIABLE_FRAME::LF => $this->localFrameStack->getLastItem(),
            E_VARIABLE_FRAME::TF => $this->temporaryFrame,
        };
    }

    /**
     * Get argument variable
     *
     * @param Argument $argument Argument instance
     * @return Variable Variable instance
     * @throws SemanticException If argument is not a variable type or variable does not exist
     * @throws FrameAccessException If variable frame is not valid
     * @throws VariableAccessException If variable frame is not valid
     */
    public function getArgumentVariable(Argument $argument): Variable
    {
        if ($argument->getType() != E_ARGUMENT_TYPE::VAR) {
            throw new SemanticException("Argument {$argument->getStringValue()} is not a variable type");
        }

        [$variableFrame, $variableName] = Variable::parseVariableName($argument->getStringValue());

        return $this->getVariableFrame($variableFrame)->getVariable($variableName);
    }

    /**
     * Check if operand is defined
     *
     * @param Argument $argument Argument instance
     * @return bool True if variable is defined, false otherwise
     * @throws SemanticException If variable definition is invalid
     * @throws FrameAccessException If variable frame is not valid
     * @throws VariableAccessException If variable does not exist
     */
    public function isOperandDefined(Argument $argument): bool
    {
        if ($argument->getType() === E_ARGUMENT_TYPE::VAR) {
            return $this->getArgumentVariable($argument)->isDefined();
        }

        return true;
    }

    /**
     * Get operand final type
     * * For variable: get variable value type
     * * Otherwise: get argument type
     *
     * @param Argument $argument Argument instance
     * @return E_ARGUMENT_TYPE Argument type
     * @throws OperandTypeException If argument is not a variable type
     * @throws SemanticException If argument type is invalid
     */
    public function getOperandFinalType(Argument $argument): E_ARGUMENT_TYPE
    {
        if ($argument->getType() == E_ARGUMENT_TYPE::VAR) {
            return $this->getArgumentVariable($argument)->getType();
        }

        return $argument->getType();
    }

    /**
     * Get operand typed value
     *
     * @param Argument|null $argument Argument instance
     * @param bool $allowNull Allow null value
     * @return string|int|bool|null Typed value
     * @throws FrameAccessException
     * @throws OperandTypeException If argument type is invalid
     * @throws SemanticException If argument is not a variable type
     * @throws VariableAccessException
     * @throws ValueException
     */
    public function getOperandTypedValue(Argument|null $argument): string|int|bool|null
    {
        if ($argument === null) {
            return null;
        }

        if ($argument->getType()->isLiteralType()) {
            return $argument->getTypedValue();
        }

        if ($argument->getType() == E_ARGUMENT_TYPE::VAR) {
            if (!$this->isOperandDefined($argument)) {
                throw new ValueException("Variable {$argument->getStringValue()} is not defined");
            }

            return $this->getArgumentVariable($argument)->getTypedValue();
        }

        throw new OperandTypeException("Invalid argument type {$argument->getType()->value}");
    }

    /**
     * Check if operand type is of the given type
     *
     * @param Argument $argument Argument instance
     * @param E_ARGUMENT_TYPE $type Type to check
     * @return bool True if operand type is of the given type, false otherwise
     * @throws OperandTypeException If operand type is invalid
     * @throws SemanticException If argument is not a variable type
     */
    public function isOperandTypeOf(Argument $argument, E_ARGUMENT_TYPE $type): bool
    {
        return $this->getOperandFinalType($argument) == $type;
    }

    /**
     * Check if operands are of the same type
     *
     * @param Argument $left Left operand
     * @param Argument $right Right operand
     * @return bool True if operands are of the same type, false otherwise
     * @throws OperandTypeException If operand type is invalid
     * @throws SemanticException If operand is not a variable type
     */
    public function isOperandSameType(Argument $left, Argument $right): bool
    {
        return $this->getOperandFinalType($left) == $this->getOperandFinalType($right);
    }

    /**
     * Output value to stdout
     *
     * @param E_ARGUMENT_TYPE $type Output value type
     * @param string|int|bool $value Output value
     * @return void
     * @throws OperandTypeException If value type is not a variable type and cannot be written to output
     */
    public function runOutput(E_ARGUMENT_TYPE $type, string|int|bool|null $value): void
    {
        switch ($type) {
            case E_ARGUMENT_TYPE::INT:
                $this->stdout->writeInt((int)$value);
                break;
            case E_ARGUMENT_TYPE::STRING:
                $this->stdout->writeString($value);
                break;
            case E_ARGUMENT_TYPE::BOOL:
                $this->stdout->writeBool($value == "true");
                break;
            case E_ARGUMENT_TYPE::NIL:
                break;
            default:
                throw new OperandTypeException("Invalid argument type $type->value for WRITE instruction");
        }
    }

    /**
     * Process ADD, SUB, MUL and IDIV instructions
     *
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If operands are not of type int
     * @throws SemanticException If result variable is not a variable type
     * @throws OperandValueException If division by zero occurs
     * @throws ValueException If variable operands do not have value
     * @throws FrameAccessException If variable frame is not valid
     */
    public function runMath(Instruction $instruction): void
    {
        global $MATH_MAP;
        $resultVariableArgument = $instruction->getArgument(0);
        $leftOperand = $instruction->getArgument(1);
        $rightOperand = $instruction->getArgument(2);

        $leftTypedValue = $this->getOperandTypedValue($leftOperand);
        $rightTypedValue = $this->getOperandTypedValue($rightOperand);

        if (!$this->isOperandTypeOf($leftOperand, E_ARGUMENT_TYPE::INT) || !$this->isOperandTypeOf($rightOperand, E_ARGUMENT_TYPE::INT)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction operands must be of type int");
        }

        $func = $MATH_MAP[$instruction->getName()->value];
        $resultVariable = $this->getArgumentVariable($resultVariableArgument);

        $resultVariable->setType(E_ARGUMENT_TYPE::INT);
        try {
            $resultVariable->setValue(strval($func($leftTypedValue, $rightTypedValue)));
        } catch (DivisionByZeroError) {
            throw new OperandValueException("Division by zero");
        }
    }

    /**
     * Process LT, GT and EQ instructions
     *
     * @param Instruction $instruction Instruction instance
     * @throws OperandTypeException If operands are not of the same type
     * @throws SemanticException If result variable is not a variable type
     */
    public function runRelational(Instruction $instruction): void
    {
        global $RELATIONAL_MAP;
        $resultVariableArgument = $instruction->getArgument(0);
        $leftOperand = $instruction->getArgument(1);
        $rightOperand = $instruction->getArgument(2);

        $leftTypedValue = $this->getOperandTypedValue($leftOperand);
        $rightTypedValue = $this->getOperandTypedValue($rightOperand);

        if (!$this->isOperandSameType($leftOperand, $rightOperand)) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction operands must be of the same type");
        }

        $func = $RELATIONAL_MAP[$instruction->getName()->value];
        $resultVariable = $this->getArgumentVariable($resultVariableArgument);

        $resultVariable->setType(E_ARGUMENT_TYPE::BOOL);
        $resultVariable->setValue($func($leftTypedValue, $rightTypedValue) ? "true" : "false");
    }

    public function runBool(Instruction $instruction): void
    {
        global $BOOL_MAP;
        $resultVariableArgument = $instruction->getArgument(0);
        $leftOperand = $instruction->getArgument(1);
        $rightOperand = $instruction->getArgument(2);

        $leftTypedValue = $this->getOperandTypedValue($leftOperand);
        $rightTypedValue = $this->getOperandTypedValue($rightOperand);

        if ($instruction->getName() === E_INSTRUCTION_NAME::NOT) {
            if (!$this->isOperandTypeOf($leftOperand, E_ARGUMENT_TYPE::BOOL)) {
                throw new OperandTypeException("NOT instruction operand must be of type bool");
            }
        } else {
            if (!$this->isOperandTypeOf($leftOperand, E_ARGUMENT_TYPE::BOOL) || !$this->isOperandTypeOf($rightOperand, E_ARGUMENT_TYPE::BOOL)) {
                throw new OperandTypeException("{$instruction->getName()->value} instruction operands must be of type bool");
            }
        }

        $func = $BOOL_MAP[$instruction->getName()->value];
        $resultVariable = $this->getArgumentVariable($resultVariableArgument);

        $resultVariable->setType(E_ARGUMENT_TYPE::BOOL);
        $resultVariable->setValue($func($leftTypedValue, $rightTypedValue) ? "true" : "false");
    }

    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();

        /** @var Instruction[] $parsedInstructions */
        $parsedInstructions = [];

        /** @var DOMElement $programElement */
        $programElement = $dom->documentElement;
        for ($index = 1; $index < $programElement->childNodes->length; $index++) {
            /** @var DOMElement $instructionElement */
            $instructionElement = $programElement->childNodes->item($index);

            if (!$instructionElement instanceof DOMElement) continue;

            $attributes = $instructionElement->attributes;

            /** @var DOMAttr $opcodeAttribute */
            $opcodeAttribute = $attributes->getNamedItem('opcode');
            if (!$opcodeAttribute || !$opcodeAttribute->value) {
                throw new XMLException("Missing opcode attribute at $index ($instructionElement->textContent)");
            }

            $orderAttribute = $attributes->getNamedItem('order');

            if (!$orderAttribute || !$orderAttribute->value || !is_numeric($orderAttribute->value)) {
                throw new XMLException("Missing order attribute at $index ($instructionElement->textContent)");
            }

            $intOrder = (int)$orderAttribute->value;

            if ($intOrder < 1) {
                throw new XMLException("Invalid order attribute at $index ($instructionElement->textContent)");
            }

            $instructionName = E_INSTRUCTION_NAME::fromValue($opcodeAttribute->nodeValue);

            $argumentElements = $instructionElement->childNodes;
            $parsedArguments = [];

            for ($argumentIndex = 0; $argumentIndex < $argumentElements->length; $argumentIndex++) {
                /** @var DOMElement $argumentElement */
                $argumentElement = $argumentElements->item($argumentIndex);
                if (!$argumentElement instanceof DOMElement) continue;

                $argumentTag = $argumentElement->tagName;
                $argumentExpectedOrder = count($parsedArguments) + 1;

                if ($argumentTag != "arg$argumentExpectedOrder") {
                    throw new XMLException("Invalid argument tag order at $index ($instructionElement->textContent)");
                }

                $argumentType = $argumentElement->attributes->getNamedItem("type")->value;

                if (!E_ARGUMENT_TYPE::containsValue($argumentType)) {
                    throw new OperandTypeException("Invalid argument type at $index ($instructionElement->textContent)");
                }

                $argument = E_ARGUMENT_TYPE::{E_ARGUMENT_TYPE::fromValue($argumentType)};

                $argumentValue = $argumentElement->nodeValue;

                $parsedArguments[] = new Argument($argumentIndex, $argumentValue, $argument);
            }

            $parsedInstructions[] = new Instruction($instructionName, $parsedArguments, (int)$orderAttribute->value);
        }

        foreach ($parsedInstructions as $instruction) {
            if ($instruction->getName() === E_INSTRUCTION_NAME::LABEL) {
                $labelArgument = $instruction->getArgument(0);

                $labelValue = $labelArgument->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

                if (array_key_exists($labelValue, $this->labels)) {
                    throw new SemanticException("Duplicate label $labelValue");
                }

                $this->labels[$labelValue] = $instruction->getOrder();
            }
        }

        for (; $this->instructionCounter < count($parsedInstructions); $this->instructionCounter++) {
            $instruction = $parsedInstructions[$this->instructionCounter];
            $executionInstruction = BuiltInInstruction::getInstruction($instruction->getName());

            $executionInstruction->getExecutionInstruction()->execute($this, $instruction);
        }

        return 0;
    }

    public function getInput(): InputReader
    {
        return $this->input;
    }
}
