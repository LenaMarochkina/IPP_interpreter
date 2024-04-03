<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\XMLException;
use IPP\Student\Exception\OperandTypeException;

use DOMElement;
use DOMAttr;
use IPP\Student\Exception\SemanticException;

class Interpreter extends AbstractInterpreter
{
    private Frame $globalFrame;

    protected function init(): void
    {
        parent::init();

        $this->globalFrame = new Frame(E_VARIABLE_FRAME::GF);
    }

    /**
     * Check if global variable exists
     *
     * @param string $name Variable name
     * @throws OperandTypeException If variable does not exist
     */
    private function checkGlobalVariableExists(string $name): void
    {
        if (!$this->globalFrame->containsVariable($name)) {
            throw new OperandTypeException("Variable $name does not exist in global frame");
        }
    }

    /**
     * Output value to stdout
     *
     * @param E_ARGUMENT_TYPE $type Output value type
     * @param string|null $value Output value
     * @return void
     * @throws OperandTypeException If value type is not a variable type and cannot be written to output
     */
    private function runOutput(E_ARGUMENT_TYPE $type, string|null $value): void
    {
        if (!$type->isVariableType()) {
            throw new OperandTypeException("Value with type $type->value cannot be written to output");
        }

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
            default:
                throw new OperandTypeException("Invalid argument type $type->value for WRITE instruction");
        }
    }

    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();

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
            $this->executeInstruction($instruction);
        }

        return 0;
    }

    /**
     * @throws OperandTypeException
     * @throws SemanticException
     */
    private function executeInstruction(Instruction $instruction): void
    {
        switch ($instruction->getName()) {
            case E_INSTRUCTION_NAME::DEFVAR:
                $argument = $instruction->getArgument(0);

                [$variableFrame, $variableName] = Variable::parseVariableName($argument->getValue());

                // TODO: create variable in specified frame

                $this->globalFrame->createVariable($variableName, $argument->getType());
                break;
            case E_INSTRUCTION_NAME::MOVE:
                [$argumentVariable, $argumentValue] = [$instruction->getArgument(0), $instruction->getArgument(1)];

                [, $argVariableName] = Variable::parseVariableName($argumentVariable->getValue());

                $this->checkGlobalVariableExists($argVariableName);

                $this->globalFrame->getVariable($argVariableName)->setType($argumentValue->getType());
                $this->globalFrame->getVariable($argVariableName)->setValue($argumentValue->getValue());

                break;
            case E_INSTRUCTION_NAME::WRITE:
                $argument = $instruction->getArgument(0);
                switch ($argument->getType()) {
                    case E_ARGUMENT_TYPE::INT:
                    case E_ARGUMENT_TYPE::STRING:
                    case E_ARGUMENT_TYPE::BOOL:
                        $this->runOutput($argument->getType(), $argument->getValue());
                        break;
                    case E_ARGUMENT_TYPE::VAR:
                        [$argVariableFrame, $argVariableName] = Variable::parseVariableName($argument->getValue());

                        // TODO: check in frame

                        $this->checkGlobalVariableExists($argVariableName);

                        $this->runOutput(
                            $this->globalFrame->getVariable($argVariableName)->getType(),
                            $this->globalFrame->getVariable($argVariableName)->getValue()
                        );

                        break;
                    default:
                        throw new OperandTypeException("Invalid argument type for WRITE instruction");
                }
                break;
            default:
                throw new SemanticException("Unknown instruction " . $instruction->getName()->value);
        }
    }
}
