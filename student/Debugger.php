<?php

namespace IPP\Student;

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
     * @throws SemanticException If some semantic error occurs
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
     * @throws SemanticException
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
                    'index' => $index + 1,
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

            if ($instruction->getName() === E_INSTRUCTION_NAME::CALL)
                $name = $instruction->getArgument(0)->getValue()->getValue();

            $table->addRow(
                $index,
                [
                    'index' => $index + 1,
                    'order' => $call,
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
                    'count' => $count,
                    'percentage' => number_format($count / $total * 100, 2) . '%',
                ]
            );
        }

        $table->setCaption('\/ Execution statistics \/');
        $table->printTable();
    }
}