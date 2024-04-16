<?php

namespace IPP\Student;

use IPP\Core\Exception\OutputFileException;
use IPP\Student\Exception\SemanticException;

class Debugger
{
    private Interpreter $interpreter;

    public function __construct(Interpreter $interpreter)
    {
        $this->interpreter = $interpreter;
    }

    /**
     * Print symbol row
     *
     * @param string $name Name of the symbol
     * @param string $frame Frame of the symbol
     * @param string $type Type of the symbol
     * @param Value $value Value of the symbol
     * @throws SemanticException If some semantic error occurs
     * @throws OutputFileException If some output file error occurs
     */
    public function printSymbolRow(
        string $name,
        string $frame,
        string $type,
        Value  $value
    ): void
    {
        $table = new TablePrinter(
            $this->interpreter->getStdErr(),
            [
                'frame' => 'Frame',
                'name' => 'Name',
                'type' => 'Type',
                'value' => 'Value',
                'mem_value' => 'Memory (Internal) Value',
            ]
        );
        $table->addRow(
            $name,
            [
                'frame' => $frame,
                'name' => $name,
                'type' => $type,
                'value' => $value->outputValueForSTDOUT(E_ARGUMENT_TYPE::fromValue($type)),
                'mem_value' => $value->getValue() ?? '',
            ]
        );
        $table->printTable();
    }

    /**
     * Print frame
     *
     * @param Frame $frame Frame to print
     * @param string|null $caption Caption of the frame
     * @throws OutputFileException If some output file error occurs
     * @throws SemanticException If some semantic error occurs
     */
    public function printFrame(Frame $frame, ?string $caption = null): void
    {
        $table = new TablePrinter(
            $this->interpreter->getStdErr(),
            [
                'frame' => 'Frame',
                'name' => 'Name',
                'type' => 'Type',
                'value' => 'Value',
                'mem_value' => 'Memory (Internal) Value',
                'defined' => 'Defined',
            ]
        );
        foreach ($frame->getVariables() as $name => $variable) {
            $table->addRow(
                $name,
                [
                    'frame' => $frame->getFrame()->value,
                    'name' => $name,
                    'type' => $variable->getType()->value,
                    'value' => $variable->getValue()->outputValueForSTDOUT($variable->getType()),
                    'mem_value' => $variable->getValue()->getValue() ?? '-',
                    'defined' => $variable->isDefined() ? 'true' : 'false',
                ]
            );
        }

        if ($caption)
            $table->setCaption($caption);

        $table->printTable();
    }

    /**
     * @throws SemanticException
     */
    public function printDataStack(): void
    {
        $table = new TablePrinter(
            $this->interpreter->getStdErr(),
            [
                'index' => 'Index',
                'type' => 'Type',
                'value' => 'Value',
            ]
        );
        foreach (array_reverse($this->interpreter->dataStack->readItems()) as $index => $value) {
            $table->addRow(
                $index,
                [
                    'index' => strval($index + 1),
                    'type' => $value->getType()->value,
                    'value' => $value->getValue()->outputValueForSTDOUT($value->getType()),
                ]
            );
        }
        $table->setCaption('\/ Data stack (new on top) \/');
        $table->printTable();
    }

    public function printCallStack(): void
    {
        $table = new TablePrinter(
            $this->interpreter->getStdErr(),
            [
                'index' => 'Index',
                'order' => 'Order',
                'opcode' => 'Opcode',
                'label' => 'Label',
            ]
        );
        foreach (array_reverse($this->interpreter->callStack->readItems()) as $index => $call) {
            $instruction = $this->interpreter->parsedInstructions[$call];

            if ($instruction->getName() === E_INSTRUCTION_NAME::CALL) {
                $argument = $instruction->getArgument(0);

                if (is_null($argument)) continue;

                $name = $argument->getValue()->getValue();
            }

            $table->addRow(
                $index,
                [
                    'index' => strval($index + 1),
                    'order' => strval($call),
                    'opcode' => $instruction->getName()->value,
                    'label' => $name ?? '-',
                ]
            );
        }
        $table->setCaption('\/ Call stack (new on top) \/');
        $table->printTable();
    }

    public function printExecutionStatistics(): void
    {
        $total = $this->interpreter->executionCounter;

        $table = new TablePrinter(
            $this->interpreter->getStdErr(),
            [
                'name' => 'Instruction Name',
                'count' => 'Executed Count',
                'percentage' => 'Percentage',
            ]
        );

        $sortedStatistics = $this->interpreter->executionStatistics;
        arsort($sortedStatistics);

        foreach ($sortedStatistics as $name => $count) {
            $table->addRow(
                $name,
                [
                    'name' => $name,
                    'count' => strval($count),
                    'percentage' => number_format($count / $total * 100, 2) . '%',
                ]
            );
        }

        $table->setCaption('\/ Execution statistics \/');
        $table->printTable();
    }
}