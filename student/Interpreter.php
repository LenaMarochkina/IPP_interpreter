<?php

namespace IPP\Student;

use DivisionByZeroError;
use Exception;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\XMLException;
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
    private Frame $globalFrame;
    private Frame|null $temporaryFrame;

    /**
     * @var GenericStack<Frame> $localFrameStack
     */
    private GenericStack $localFrameStack;

    /**
     * @var array{string: int} $labels Labels
     */
    private array $labels = [];

    /**
     * @var GenericStack<int> $callStack Call stack
     */
    private GenericStack $callStack;

    /**
     * @var GenericStack<int|string|bool|null> $dataStack Data stack
     */
    private GenericStack $dataStack;

    private int $instructionCounter = 0;

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
    private function validateVariableFrame(E_VARIABLE_FRAME $frame): void
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
    private function getVariableFrame(E_VARIABLE_FRAME $frame): Frame
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
    private function getArgumentVariable(Argument $argument): Variable
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
    private function isOperandDefined(Argument $argument): bool
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
    private function getOperandFinalType(Argument $argument): E_ARGUMENT_TYPE
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
    private function getOperandTypedValue(Argument|null $argument): string|int|bool|null
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
    private function isOperandTypeOf(Argument $argument, E_ARGUMENT_TYPE $type): bool
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
    private function isOperandSameType(Argument $left, Argument $right): bool
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
    private function runOutput(E_ARGUMENT_TYPE $type, string|int|bool|null $value): void
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
    private function runMath(Instruction $instruction): void
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
    private function runRelational(Instruction $instruction): void
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

    private function runBool(Instruction $instruction): void
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
            $this->executeInstruction($instruction);
        }

        return 0;
    }

    /**
     * @throws OperandTypeException
     * @throws SemanticException
     * @throws FrameAccessException
     * @throws Exception
     */
    private function executeInstruction(Instruction $instruction): void
    {
        switch ($instruction->getName()) {
            case E_INSTRUCTION_NAME::CREATEFRAME:
                $this->temporaryFrame = new Frame(E_VARIABLE_FRAME::TF);
                break;
            case E_INSTRUCTION_NAME::PUSHFRAME:
                if ($this->temporaryFrame === null) {
                    throw new FrameAccessException("Temporary frame does not exist");
                }

                $this->localFrameStack->push($this->temporaryFrame);
                $this->temporaryFrame = null;
                break;
            case E_INSTRUCTION_NAME::POPFRAME:
                if ($this->localFrameStack->isEmpty()) {
                    throw new FrameAccessException("Local frame does not exist");
                }

                $this->temporaryFrame = $this->localFrameStack->pop();
                break;
            case E_INSTRUCTION_NAME::DEFVAR:
                $argument = $instruction->getArgument(0);

                [$variableFrame, $variableName] = Variable::parseVariableName($argument->getStringValue());

                $this->getVariableFrame($variableFrame)->createVariable($variableName, $argument->getType());
                break;
            case E_INSTRUCTION_NAME::MOVE:
                [$argumentVariable, $argumentValue] = [$instruction->getArgument(0), $instruction->getArgument(1)];

                $argumentValueType = $this->getOperandFinalType($argumentValue);
                $argumentValueValue = Value::getTypedValueString($argumentValueType, $this->getOperandTypedValue($argumentValue));

                $this->getArgumentVariable($argumentVariable)->setDefined(true);
                $this->getArgumentVariable($argumentVariable)->setType($argumentValueType);
                $this->getArgumentVariable($argumentVariable)->setValue($argumentValueValue);

                break;
            case E_INSTRUCTION_NAME::CALL:
                [
                    $argumentLabel,
                ] = [
                    $instruction->getArgument(0),
                ];

                $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

                if (!array_key_exists($labelValue, $this->labels))
                    throw new SemanticException("Unknown label $labelValue");

                $this->callStack->push($this->instructionCounter);

                $this->instructionCounter = $this->labels[$labelValue] - 1;

                break;
            case E_INSTRUCTION_NAME::RETURN:
                if ($this->callStack->isEmpty()) {
                    throw new ValueException("Call stack is empty");
                }

                $this->instructionCounter = $this->callStack->pop();

                break;
            case E_INSTRUCTION_NAME::PUSHS:
                $argument = $instruction->getArgument(0);

                $argumentValue = $this->getOperandTypedValue($argument);

                $this->dataStack->push($argumentValue);

                break;
            case E_INSTRUCTION_NAME::POPS:
                $argument = $instruction->getArgument(0);

                if ($this->dataStack->isEmpty()) {
                    throw new ValueException("Data stack is empty");
                }

                $value = $this->dataStack->pop();
                $detectedType = Value::determineValueType($value);

                $this->getArgumentVariable($argument)->setDefined(true);
                $this->getArgumentVariable($argument)->setType($detectedType);
                $this->getArgumentVariable($argument)->setValue(Value::getTypedValueString($detectedType, $value));

                break;
            case E_INSTRUCTION_NAME::READ:
                [
                    $argumentVariable,
                    $argumentType,
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                ];

                $input = match ($argumentType->getValue()->getValue()) {
                    "int" => $this->input->readInt(),
                    "string" => $this->input->readString(),
                    "bool" => $this->input->readBool(),
                    default => throw new OperandTypeException("Invalid argument type for READ instruction"),
                };
                $detectedType = Value::determineValueType($input);

                $this->getArgumentVariable($argumentVariable)->setType($input !== null ? $detectedType : E_ARGUMENT_TYPE::NIL);
                $this->getArgumentVariable($argumentVariable)->setValue($input !== null ? Value::getTypedValueString($detectedType, $input) : "");

                break;
            case E_INSTRUCTION_NAME::WRITE:
                $argument = $instruction->getArgument(0);
                switch ($argument->getType()) {
                    case E_ARGUMENT_TYPE::INT:
                    case E_ARGUMENT_TYPE::STRING:
                    case E_ARGUMENT_TYPE::BOOL:
                    case E_ARGUMENT_TYPE::NIL:
                        $this->runOutput($argument->getType(), $argument->getTypedValue());

                        break;
                    case E_ARGUMENT_TYPE::VAR:
                        if (!$this->isOperandDefined($argument)) {
                            throw new ValueException("Variable {$argument->getStringValue()} is not defined");
                        }

                        $this->runOutput(
                            $this->getArgumentVariable($argument)->getType(),
                            $this->getArgumentVariable($argument)->getTypedValue(),
                        );

                        break;
                    default:
                        throw new OperandTypeException("Invalid argument type for WRITE instruction");
                }
                break;

            case E_INSTRUCTION_NAME::ADD:
            case E_INSTRUCTION_NAME::SUB:
            case E_INSTRUCTION_NAME::MUL:
            case E_INSTRUCTION_NAME::IDIV:
                $this->runMath($instruction);
                break;
            case E_INSTRUCTION_NAME::LT:
            case E_INSTRUCTION_NAME::GT:
            case E_INSTRUCTION_NAME::EQ:
                $this->runRelational($instruction);
                break;
            case E_INSTRUCTION_NAME::AND:
            case E_INSTRUCTION_NAME::OR:
            case E_INSTRUCTION_NAME::NOT:
                $this->runBool($instruction);
                break;
            case E_INSTRUCTION_NAME::INT2CHAR:
                [$argumentVariable, $argumentNumber] = [$instruction->getArgument(0), $instruction->getArgument(1)];

                $argumentNumberValue = $this->getOperandTypedValue($argumentNumber);

                if (!$this->isOperandTypeOf($argumentNumber, E_ARGUMENT_TYPE::INT)) {
                    throw new OperandTypeException("INT2CHAR instruction operand must be of type int");
                }

                $value = mb_chr($argumentNumberValue);

                if (!$value) {
                    throw new StringOperationException("INT2CHAR instruction value out of bounds: $argumentNumberValue");
                }

                $this->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
                $this->getArgumentVariable($argumentVariable)->setValue($value);

                break;
            case E_INSTRUCTION_NAME::STRI2INT:
                [
                    $argumentVariable,
                    $argumentString,
                    $argumentIndex
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                    $instruction->getArgument(2),
                ];

                $argumentStringValue = $this->getOperandTypedValue($argumentString);
                $argumentIndexValue = $this->getOperandTypedValue($argumentIndex);

                if (!$this->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
                    throw new OperandTypeException("STRI2INT instruction first operand must be of type string");
                }

                if (!$this->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
                    throw new OperandTypeException("STRI2INT instruction second operand must be of type int");
                }

                if ($argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentStringValue)) {
                    throw new StringOperationException("STRI2INT instruction index out of bounds: $argumentIndexValue >= " . mb_strlen($argumentStringValue));
                }

                $charCode = mb_ord($argumentStringValue[$argumentIndexValue]);

                $this->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::INT);
                $this->getArgumentVariable($argumentVariable)->setValue(strval($charCode));

                break;
            case E_INSTRUCTION_NAME::CONCAT:
                [
                    $argumentVariable,
                    $argumentLeftString,
                    $argumentRightString
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                    $instruction->getArgument(2),
                ];

                $argumentLeftStringValue = $this->getOperandTypedValue($argumentLeftString);
                $argumentRightStringValue = $this->getOperandTypedValue($argumentRightString);

                if (!$this->isOperandTypeOf($argumentLeftString, E_ARGUMENT_TYPE::STRING) || !$this->isOperandTypeOf($argumentRightString, E_ARGUMENT_TYPE::STRING)) {
                    throw new OperandTypeException("CONCAT instruction operands must be of type string");
                }

                $this->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
                $this->getArgumentVariable($argumentVariable)->setValue($argumentLeftStringValue . $argumentRightStringValue);

                break;
            case E_INSTRUCTION_NAME::STRLEN:
                [
                    $argumentVariable,
                    $argumentString,
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                ];

                $argumentStringValue = $this->getOperandTypedValue($argumentString);

                if (!$this->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
                    throw new OperandTypeException("STRLEN instruction operand must be of type string");
                }

                $this->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::INT);
                $this->getArgumentVariable($argumentVariable)->setValue(strval(mb_strlen($argumentStringValue)));

                break;
            case E_INSTRUCTION_NAME::GETCHAR:
                [
                    $argumentVariable,
                    $argumentString,
                    $argumentIndex
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                    $instruction->getArgument(2),
                ];

                $argumentStringValue = $this->getOperandTypedValue($argumentString);
                $argumentIndexValue = $this->getOperandTypedValue($argumentIndex);

                if (!$this->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
                    throw new OperandTypeException("GETCHAR instruction second operand must be of type string");
                }

                if (!$this->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
                    throw new OperandTypeException("GETCHAR instruction third operand must be of type int");
                }

                if ($argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentStringValue)) {
                    throw new StringOperationException("GETCHAR instruction index out of bounds: $argumentIndexValue >= " . mb_strlen($argumentStringValue));
                }

                $this->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
                $this->getArgumentVariable($argumentVariable)->setValue($argumentStringValue[$argumentIndexValue]);

                break;
            case E_INSTRUCTION_NAME::SETCHAR:
                [
                    $argumentVariable,
                    $argumentIndex,
                    $argumentString
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                    $instruction->getArgument(2),
                ];

                $argumentVariableValue = $this->getOperandTypedValue($argumentVariable);
                $argumentIndexValue = $this->getOperandTypedValue($argumentIndex);
                $argumentStringValue = $this->getOperandTypedValue($argumentString);

                if (!$this->isOperandTypeOf($argumentVariable, E_ARGUMENT_TYPE::STRING)) {
                    throw new OperandTypeException("SETCHAR instruction first operand must be of type string");
                }

                if (!$this->isOperandTypeOf($argumentIndex, E_ARGUMENT_TYPE::INT)) {
                    throw new OperandTypeException("SETCHAR instruction second operand must be of type int");
                }

                if (!$this->isOperandTypeOf($argumentString, E_ARGUMENT_TYPE::STRING)) {
                    throw new OperandTypeException("SETCHAR instruction third operand must be of type string");
                }

                if ($argumentIndexValue < 0 || $argumentIndexValue >= mb_strlen($argumentVariableValue)) {
                    throw new StringOperationException("SETCHAR instruction index out of bounds: $argumentIndexValue >= " . mb_strlen($argumentVariableValue));
                }

                if (mb_strlen($argumentStringValue) < 1) {
                    throw new StringOperationException("SETCHAR instruction value length must be greater than 0");
                }

                $argumentVariableValue[$argumentIndexValue] = $argumentStringValue[0];

                $this->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
                $this->getArgumentVariable($argumentVariable)->setValue($argumentVariableValue);

                break;
            case E_INSTRUCTION_NAME::TYPE:
                [
                    $argumentVariable,
                    $argumentSymbol,
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                ];

                $symbolType = $this->isOperandDefined($argumentSymbol) ? $this->getOperandFinalType($argumentSymbol)->value : "";

                $this->getArgumentVariable($argumentVariable)->setType(E_ARGUMENT_TYPE::STRING);
                $this->getArgumentVariable($argumentVariable)->setValue($symbolType);
                break;
            case E_INSTRUCTION_NAME::LABEL:
                // Ignored because labels are processed before the instructions
                break;
            case E_INSTRUCTION_NAME::JUMP:
                [
                    $argumentLabel,
                ] = [
                    $instruction->getArgument(0),
                ];

                $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

                if (!array_key_exists($labelValue, $this->labels))
                    throw new SemanticException("Unknown label $labelValue");

                $this->instructionCounter = $this->labels[$labelValue] - 1;

                break;
            case E_INSTRUCTION_NAME::JUMPIFNEQ:
            case E_INSTRUCTION_NAME::JUMPIFEQ:
                [
                    $argumentLabel,
                    $argumentLeftSymbol,
                    $argumentRightSymbol,
                ] = [
                    $instruction->getArgument(0),
                    $instruction->getArgument(1),
                    $instruction->getArgument(2),
                ];

                $labelValue = $argumentLabel->getValue()->getTypedValue(E_ARGUMENT_TYPE::STRING);

                if (!array_key_exists($labelValue, $this->labels))
                    throw new SemanticException("Unknown label $labelValue");

                $leftSymbolValue = $this->getOperandTypedValue($argumentLeftSymbol);
                $rightSymbolValue = $this->getOperandTypedValue($argumentRightSymbol);
                $leftSymbolType = $this->getOperandFinalType($argumentLeftSymbol);
                $rightSymbolType = $this->getOperandFinalType($argumentRightSymbol);

                if (!($this->isOperandSameType($argumentLeftSymbol, $argumentRightSymbol) || $leftSymbolType === E_ARGUMENT_TYPE::NIL || $rightSymbolType === E_ARGUMENT_TYPE::NIL)) {
                    throw new OperandTypeException("JUMPIFEQ instruction operands must be of the same type or one of them must be nil");
                }

                $isValueValid = $instruction->getName() === E_INSTRUCTION_NAME::JUMPIFEQ
                    ? $leftSymbolValue === $rightSymbolValue
                    : $leftSymbolValue !== $rightSymbolValue;

                if ($isValueValid)
                    $this->instructionCounter = $this->labels[$labelValue] - 1;

                break;
            case E_INSTRUCTION_NAME::EXIT:
                [
                    $argumentSymbol,
                ] = [
                    $instruction->getArgument(0),
                ];

                $argumentSymbolValue = $this->getOperandTypedValue($argumentSymbol);

                if (!$this->isOperandTypeOf($argumentSymbol, E_ARGUMENT_TYPE::INT)) {
                    throw new OperandTypeException("EXIT instruction operand must be of type int");
                }

                if ($argumentSymbolValue < 0 || $argumentSymbolValue > 9) {
                    throw new OperandValueException("EXIT instruction operand must be in range 0-9");
                }

                exit($argumentSymbolValue);
            default:
                throw new SemanticException("Unknown instruction " . $instruction->getName()->value);
        }
    }
}
