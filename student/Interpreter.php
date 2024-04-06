<?php

namespace IPP\Student;

use DivisionByZeroError;

use Exception;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\XMLException;
use IPP\Core\Interface\InputReader;
use IPP\Student\Core\StreamWriter;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandTypeException;

use DOMElement;
use DOMAttr;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\VariableAccessException;

global $MATH_MAP;
/**
 * Math map
 *
 * @var array{E_INSTRUCTION_NAME: callable(int|float, int|float): int} $MATH_MAP
 */
$MATH_MAP = [
    E_INSTRUCTION_NAME::ADD->value => fn(mixed $a, mixed $b) => $a + $b,
    E_INSTRUCTION_NAME::ADDS->value => fn(mixed $a, mixed $b) => $a + $b,
    E_INSTRUCTION_NAME::SUB->value => fn(mixed $a, mixed $b) => $a - $b,
    E_INSTRUCTION_NAME::SUBS->value => fn(mixed $a, mixed $b) => $a - $b,
    E_INSTRUCTION_NAME::MUL->value => fn(mixed $a, mixed $b) => $a * $b,
    E_INSTRUCTION_NAME::MULS->value => fn(mixed $a, mixed $b) => $a * $b,
    E_INSTRUCTION_NAME::IDIV->value => fn(int $a, int $b) => intval($a / $b),
    E_INSTRUCTION_NAME::IDIVS->value => fn(int $a, int $b) => intval($a / $b),
    E_INSTRUCTION_NAME::DIV->value => fn(float $a, float $b) => $a / $b,
    E_INSTRUCTION_NAME::DIVS->value => fn(float $a, float $b) => $a / $b,
];
global $RELATIONAL_MAP;

/**
 * Relational map
 *
 * @var array{E_INSTRUCTION_NAME: callable(mixed, mixed): bool} $RELATIONAL_MAP
 */
$RELATIONAL_MAP = [
    E_INSTRUCTION_NAME::LT->value => fn(mixed $a, mixed $b) => $a < $b,
    E_INSTRUCTION_NAME::LTS->value => fn(mixed $a, mixed $b) => $a < $b,
    E_INSTRUCTION_NAME::GT->value => fn(mixed $a, mixed $b) => $a > $b,
    E_INSTRUCTION_NAME::GTS->value => fn(mixed $a, mixed $b) => $a > $b,
    E_INSTRUCTION_NAME::EQ->value => fn(mixed $a, mixed $b) => $a === $b,
    E_INSTRUCTION_NAME::EQS->value => fn(mixed $a, mixed $b) => $a === $b,
];

global $BOOL_MAP;

/**
 * Bool map
 *
 * @var array{E_INSTRUCTION_NAME: callable(bool, bool): bool} $BOOL_MAP
 */
$BOOL_MAP = [
    E_INSTRUCTION_NAME::AND->value => fn(bool $a, bool $b) => $a && $b,
    E_INSTRUCTION_NAME::ANDS->value => fn(bool $a, bool $b) => $a && $b,
    E_INSTRUCTION_NAME::OR->value => fn(bool $a, bool $b) => $a || $b,
    E_INSTRUCTION_NAME::ORS->value => fn(bool $a, bool $b) => $a || $b,
    E_INSTRUCTION_NAME::NOT->value => fn(bool $a) => !$a,
    E_INSTRUCTION_NAME::NOTS->value => fn(bool $a) => !$a,
];

class Interpreter extends AbstractInterpreter
{
    /** @var string[] */
    protected array $longOptions = [
        "help",
        "source:",
        "input:"
    ];

    public Frame $globalFrame;
    public Frame|null $temporaryFrame;

    /**
     * @var GenericStack<Frame> $localFrameStack
     */
    public GenericStack $localFrameStack;

    /**
     * @var array<string, int> $labels Labels
     */
    public array $labels = [];

    /**
     * @var GenericStack<int> $callStack Call stack
     */
    public GenericStack $callStack;

    /**
     * @var GenericStack<Argument> $dataStack Data stack
     */
    public GenericStack $dataStack;

    public int $instructionCounter = 0;

    protected function init(): void
    {
        parent::init();

        $options = getopt("", $this->longOptions, $restIndex);
        $options = $options ?: [];

        $this->globalFrame = new Frame(E_VARIABLE_FRAME::GF);
        $this->localFrameStack = new GenericStack();
        $this->temporaryFrame = null;

        $this->callStack = new GenericStack();
        $this->dataStack = new GenericStack();

        $this->stdout = new StreamWriter(STDOUT);
        $this->stderr = new StreamWriter(STDERR);
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
     * @return Frame|null Frame instance
     * @throws FrameAccessException If frame is not valid
     */
    public function getVariableFrame(E_VARIABLE_FRAME $frame): Frame|null
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

        if (!is_string($argument->getStringValue())) {
            throw new SemanticException("Argument {$argument->getStringValue()} is not a string");
        }

        [$variableFrame, $variableName] = Variable::parseVariableName($argument->getStringValue());

        $frame = $this->getVariableFrame($variableFrame);

        if ($frame === null) {
            throw new FrameAccessException("Variable frame $variableFrame->value does not exist");
        }

        return $frame->getVariable($variableName);
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
     * @throws FrameAccessException If variable frame is not valid
     * @throws SemanticException If argument type is invalid
     * @throws VariableAccessException If variable does not exist
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
     * @return string|float|int|bool|null Typed value
     * @throws FrameAccessException If variable frame is not valid
     * @throws OperandTypeException If argument type is invalid
     * @throws SemanticException If argument is not a variable type
     * @throws VariableAccessException If variable does not exist
     * @throws ValueException If variable does not have value
     */
    public function getOperandTypedValue(Argument|null $argument): string|float|int|bool|null
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
     * @throws FrameAccessException If variable frame is not valid
     * @throws SemanticException If argument is not a variable type
     * @throws VariableAccessException If variable does not exist
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
     * @throws FrameAccessException If variable frame is not valid
     * @throws SemanticException If operand is not a variable type
     * @throws VariableAccessException If variable does not exist
     */
    public function isOperandSameType(Argument $left, Argument $right): bool
    {
        return $this->getOperandFinalType($left) == $this->getOperandFinalType($right);
    }

    /**
     * Output value to stdout
     *
     * @param E_ARGUMENT_TYPE $type Output value type
     * @param string|float|int|bool|null $value Output value
     * @throws OperandTypeException If value type is not a variable type and cannot be written to output
     */
    public function runOutput(E_ARGUMENT_TYPE $type, string|float|int|bool|null $value): void
    {
        switch ($type) {
            case E_ARGUMENT_TYPE::INT:
                $this->stdout->writeInt((int)$value);
                break;
            case E_ARGUMENT_TYPE::STRING:
                $this->stdout->writeString((string)$value);
                break;
            case E_ARGUMENT_TYPE::BOOL:
                $this->stdout->writeBool($value == "true");
                break;
            case E_ARGUMENT_TYPE::FLOAT:
                $this->stdout->writeFloat((float)$value);
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
     * @throws FrameAccessException If variable frame is not valid
     * @throws OperandTypeException If operands are not of type int
     * @throws OperandValueException If division by zero occurs
     * @throws SemanticException If result variable is not a variable type
     * @throws ValueException If variable operands do not have value
     * @throws VariableAccessException If variable does not exist
     * @throws Exception If some other error occurs
     */
    public function runMath(Instruction $instruction, ?E_ARGUMENT_TYPE $checkType = null): void
    {
        $isFromStack = $instruction->getIsStackInstruction();

        global $MATH_MAP;
        $resultVariableArgument = $instruction->getArgument(0);
        $rightOperand = !$isFromStack ? $instruction->getArgument(2) : $this->dataStack->pop();
        $leftOperand = !$isFromStack ? $instruction->getArgument(1) : $this->dataStack->pop();

        if ((!$isFromStack && $resultVariableArgument === null) || $leftOperand === null || $rightOperand === null) {
            throw new SemanticException("Invalid number of arguments for {$instruction->getName()->value} instruction");
        }

        $leftTypedValue = $this->getOperandTypedValue($leftOperand);
        $rightTypedValue = $this->getOperandTypedValue($rightOperand);

        if (!$checkType) {
            if (
                !($this->isOperandTypeOf($leftOperand, E_ARGUMENT_TYPE::INT) && $this->isOperandTypeOf($rightOperand, E_ARGUMENT_TYPE::INT)) &&
                !($this->isOperandTypeOf($leftOperand, E_ARGUMENT_TYPE::FLOAT) && $this->isOperandTypeOf($rightOperand, E_ARGUMENT_TYPE::FLOAT))
            ) {
                throw new OperandTypeException("Both {$instruction->getName()->value} instruction operands must be of type int or float");
            }
        } else {
            if (!($this->isOperandTypeOf($leftOperand, $checkType) && $this->isOperandTypeOf($rightOperand, $checkType))) {
                throw new OperandTypeException("Both {$instruction->getName()->value} instruction operands must be of type $checkType->value");
            }
        }

        $func = $MATH_MAP[$instruction->getName()->value];

        $resultType = match ($this->getOperandFinalType($leftOperand)) {
            E_ARGUMENT_TYPE::INT => E_ARGUMENT_TYPE::INT,
            E_ARGUMENT_TYPE::FLOAT => E_ARGUMENT_TYPE::FLOAT,
            default => throw new OperandTypeException("Invalid argument type {$this->getOperandFinalType($leftOperand)->value}"),
        };

        try {
            $resultValue = Value::getTypedValueString($resultType, $func($leftTypedValue, $rightTypedValue));

            if (!$isFromStack) {
                $resultVariable = $this->getArgumentVariable($resultVariableArgument);

                $resultVariable->setType($resultType);
                $resultVariable->setValue($resultValue);
            } else {
                $stackValue = new Argument($resultValue, $resultType);
                $this->dataStack->push($stackValue);
            }
        } catch (DivisionByZeroError) {
            throw new OperandValueException("Division by zero");
        }
    }

    /**
     * Process LT, GT and EQ instructions
     *
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If variable frame is not valid
     * @throws OperandTypeException If operands are not of the same type
     * @throws SemanticException If result variable is not a variable type
     * @throws ValueException If variable operands do not have value
     * @throws VariableAccessException If variable does not exist
     */
    public function runRelational(Instruction $instruction): void
    {
        $isFromStack = $instruction->getIsStackInstruction();

        global $RELATIONAL_MAP;
        $resultVariableArgument = $instruction->getArgument(0);
        $rightOperand = !$isFromStack ? $instruction->getArgument(2) : $this->dataStack->pop();
        $leftOperand = !$isFromStack ? $instruction->getArgument(1) : $this->dataStack->pop();

        if ((!$isFromStack && $resultVariableArgument === null) || $leftOperand === null || $rightOperand === null) {
            throw new SemanticException("Invalid number of arguments for {$instruction->getName()->value} instruction");
        }

        $leftTypedValue = $this->getOperandTypedValue($leftOperand);
        $rightTypedValue = $this->getOperandTypedValue($rightOperand);

        if (
            ($instruction->getName() === E_INSTRUCTION_NAME::LT || $instruction->getName() === E_INSTRUCTION_NAME::GT) &&
            (($leftTypedValue === null || $rightTypedValue === null) || !$this->isOperandSameType($leftOperand, $rightOperand))
        ) {
            throw new OperandTypeException("{$instruction->getName()->value} instruction operands must be of the same type");
        }

        if (
            ($instruction->getName() === E_INSTRUCTION_NAME::EQ) &&
            ($leftTypedValue !== null && $rightTypedValue !== null) &&
            !$this->isOperandSameType($leftOperand, $rightOperand)
        ) {
            throw new OperandTypeException("EQ instruction operands must be of the same type");
        }

        $func = $RELATIONAL_MAP[$instruction->getName()->value];
        $resultValue = $func($leftTypedValue, $rightTypedValue) ? "true" : "false";

        if (!$isFromStack) {
            $resultVariable = $this->getArgumentVariable($resultVariableArgument);

            $resultVariable->setType(E_ARGUMENT_TYPE::BOOL);
            $resultVariable->setValue($resultValue);
        } else {
            $stackValue = new Argument($resultValue, E_ARGUMENT_TYPE::BOOL);
            $this->dataStack->push($stackValue);
        }
    }

    /**
     * Process AND, OR and NOT instructions
     *
     * @param Instruction $instruction Instruction instance
     * @throws FrameAccessException If variable frame is not valid
     * @throws OperandTypeException If operands are not of type bool
     * @throws SemanticException If result variable is not a variable type
     * @throws ValueException If variable operands do not have value
     * @throws VariableAccessException If variable does not exist
     */
    public function runBool(Instruction $instruction): void
    {
        $isFromStack = $instruction->getIsStackInstruction();

        global $BOOL_MAP;
        $resultVariableArgument = $instruction->getArgument(0);
        $rightOperand = !$isFromStack
            ? $instruction->getArgument(2)
            : (
            $instruction->getName() !== E_INSTRUCTION_NAME::NOTS
                ? $this->dataStack->pop()
                : null
            );
        $leftOperand = !$isFromStack ? $instruction->getArgument(1) : $this->dataStack->pop();

        if ((!$isFromStack && $resultVariableArgument === null) || $leftOperand === null) {
            throw new SemanticException("Invalid number of arguments for {$instruction->getName()->value} instruction");
        }

        if ($instruction->getName() === E_INSTRUCTION_NAME::NOT || $instruction->getName() === E_INSTRUCTION_NAME::NOTS) {
            if ($rightOperand !== null) {
                throw new SemanticException("Invalid number of arguments for {$instruction->getName()->value} instruction");
            }
        } else {
            if ($rightOperand === null) {
                throw new SemanticException("Invalid number of arguments for {$instruction->getName()->value} instruction");
            }
        }

        $leftTypedValue = $this->getOperandTypedValue($leftOperand);
        $rightTypedValue = $this->getOperandTypedValue($rightOperand);

        if ($instruction->getName() === E_INSTRUCTION_NAME::NOT || $instruction->getName() === E_INSTRUCTION_NAME::NOTS) {
            if (!$this->isOperandTypeOf($leftOperand, E_ARGUMENT_TYPE::BOOL)) {
                throw new OperandTypeException("{$instruction->getName()->value} instruction operand must be of type bool");
            }
        } else {
            if (!$this->isOperandTypeOf($leftOperand, E_ARGUMENT_TYPE::BOOL) || !$this->isOperandTypeOf($rightOperand, E_ARGUMENT_TYPE::BOOL)) {
                throw new OperandTypeException("{$instruction->getName()->value} instruction operands must be of type bool");
            }
        }

        $func = $BOOL_MAP[$instruction->getName()->value];
        $resultValue = $func($leftTypedValue, $rightTypedValue) ? "true" : "false";

        if (!$isFromStack) {
            $resultVariable = $this->getArgumentVariable($resultVariableArgument);

            $resultVariable->setType(E_ARGUMENT_TYPE::BOOL);
            $resultVariable->setValue($resultValue);
        } else {
            $stackValue = new Argument($resultValue, E_ARGUMENT_TYPE::BOOL);
            $this->dataStack->push($stackValue);
        }
    }

    /**
     * Process and interpret xml source
     *
     * @return int Exit code
     * @throws OperandTypeException If some operand has wrong type
     * @throws SemanticException If some semantic error occurs
     * @throws XMLException If XML parsing error occurs
     */
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

            /** @var DOMAttr|null $opcodeAttribute */
            $opcodeAttribute = $attributes->getNamedItem('opcode');
            if (!$opcodeAttribute || !$opcodeAttribute->value) {
                throw new XMLException("Missing opcode attribute at $index ($instructionElement->textContent)");
            }

            /** @var DOMAttr|null $orderAttribute */
            $orderAttribute = $attributes->getNamedItem('order');

            if (!$orderAttribute || !$orderAttribute->value || !is_numeric($orderAttribute->value)) {
                throw new XMLException("Missing order attribute at $index ($instructionElement->textContent)");
            }

            $intOrder = (int)$orderAttribute->value;

            if ($intOrder < 1) {
                throw new XMLException("Invalid order attribute at $index ($instructionElement->textContent)");
            }

            if (!$opcodeAttribute->nodeValue) {
                throw new XMLException("Missing opcode attribute at $index ($instructionElement->textContent)");
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

                /** @var DOMAttr|null $argumentTypeAttribute */
                $argumentTypeAttribute = $argumentElement->attributes->getNamedItem("type");

                if (!$argumentTypeAttribute || !$argumentTypeAttribute->value) {
                    throw new XMLException("Missing argument type attribute at $index ($instructionElement->textContent)");
                }

                $argumentType = $argumentTypeAttribute->value;

                if (!E_ARGUMENT_TYPE::containsValue($argumentType)) {
                    throw new OperandTypeException("Invalid argument type at $index ($instructionElement->textContent)");
                }

                $argument = E_ARGUMENT_TYPE::fromValue($argumentType);

                $argumentValue = $argumentElement->nodeValue;

                $parsedArguments[] = new Argument($argumentValue, $argument);
            }

            $parsedInstructions[] = new Instruction($instructionName, $parsedArguments, (int)$orderAttribute->value);
        }

        foreach ($parsedInstructions as $instruction) {
            if ($instruction->getName() === E_INSTRUCTION_NAME::LABEL) {
                $labelArgument = $instruction->getArgument(0);

                if ($labelArgument === null) {
                    throw new SemanticException("Invalid number of arguments for LABEL instruction");
                }

                $labelValue = $labelArgument->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

                if (!is_string($labelValue)) {
                    throw new SemanticException("Invalid label value");
                }

                if (array_key_exists($labelValue, $this->labels)) {
                    throw new SemanticException("Duplicate label $labelValue");
                }

                $this->labels[$labelValue] = $instruction->getOrder();
            }
        }

        for (; $this->instructionCounter < count($parsedInstructions); $this->instructionCounter++) {
            $instruction = $parsedInstructions[$this->instructionCounter];
            $buildInInstruction = BuiltInInstruction::getInstruction($instruction->getName());
            $executionInstruction = $buildInInstruction->getExecutionInstruction();

            if ($executionInstruction === null) continue;

            $executionInstruction->execute($this, $instruction);
        }

        return 0;
    }

    public function getInput(): InputReader
    {
        return $this->input;
    }
}
