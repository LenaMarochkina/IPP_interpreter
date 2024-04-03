<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\XMLException;
use IPP\Student\Exception\OperandTypeException;

$a = new \DS\Stack();

use DOMElement;
use DOMNode;
use DOMAttr;
use IPP\Student\Exception\SemanticException;

class Interpreter extends AbstractInterpreter
{
    /** @var Variable[] internal variables */
    private array $variables = [];

    private function containsGlobalVariable(string $name): bool
    {
        foreach ($this->variables as $variable) {
            if ($variable->getName() == $name && $variable->getFrame() == E_VARIABLE_FRAME::GF) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $name
     * @return void
     * @throws OperandTypeException
     */
    private function checkGlobalVariableExists(string $name): void
    {
        if (!$this->containsGlobalVariable($name)) {
            throw new OperandTypeException("Variable $name does not exist");
        }
    }

    public function execute(): int
    {
        // TODO: Start your code here
        //Check \IPP\Core\AbstractInterpreter for predefined I/O objects:
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

            $instructionName = E_INSTRUCTION_NAME::{E_INSTRUCTION_NAME::fromValue($opcodeAttribute->nodeValue)};

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

//        $val = $this->input->readInt();
//        // WRITE string@hello!
//        if ($val) $this->stdout->writeInt($val);
//        $this->stdout->writeString("\n");
//
//        $val = $this->input->readString();
//        if ($val) $this->stdout->writeString($val);
//        $this->stdout->writeString("\n");
//        throw new NotImplementedException;

        return 0;
    }

    /**
     * @throws OperandTypeException
     * @throws SemanticException
     */
    public function executeInstruction(Instruction $instruction): void
    {
        switch ($instruction->getName()) {
            case E_INSTRUCTION_NAME::DEFVAR:
                $argument = $instruction->getArguments()[0];

                $this->variables[] = Variable::parseVariableName($argument->getValue());
                break;
            case E_INSTRUCTION_NAME::MOVE:
                $argumentVariable = $instruction->getArguments()[0];
                $argumentValue = $instruction->getArguments()[1];

                [, $argVariableName] = explode('@', $argumentVariable->getValue());

                $this->checkGlobalVariableExists($argVariableName);

                foreach ($this->variables as $variable) {
                    if ($variable->getName() == $argVariableName) {
                        $variable->setValue($argumentValue->getValue());
                        break;
                    }
                }

                break;
            case E_INSTRUCTION_NAME::WRITE:
                $argument = $instruction->getArguments()[0];
                switch ($argument->getType()) {
                    case E_ARGUMENT_TYPE::INT:
                        $this->stdout->writeInt((int)$argument->getValue());
                        break;
                    case E_ARGUMENT_TYPE::STRING:
                        $this->stdout->writeString($argument->getValue());
                        break;
                    case E_ARGUMENT_TYPE::BOOL:
                        $this->stdout->writeBool($argument->getValue() == "true");
                        break;
                    case E_ARGUMENT_TYPE::VAR:
                        foreach ($this->variables as $variable) {
                            [$argVariableFrame, $argVariableName] = explode('@', $argument->getValue());

                            $this->checkGlobalVariableExists($argVariableName);

                            if ($variable->getName() == $argVariableName) {
                                $this->stdout->writeString($variable->getValue());
                                break;
                            }
                        }
                        break;
                    default:
                        throw new OperandTypeException("Invalid argument type for WRITE instruction");
                }
                break;
            default:
                throw new SemanticException("Unknown instruction " . $instruction->getName());
        }
    }
}
