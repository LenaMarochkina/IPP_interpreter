<?php

namespace IPP\Student\Instructions;

use IPP\Student\Exception\SemanticException;
use IPP\Student\Instruction;
use IPP\Student\Interpreter;

class BREAKInstruction extends AbstractInstruction
{
    /**
     * Execute BREAK instruction
     * Prints the state of the interpreter
     *
     * @param Interpreter $interpreter Interpreter instance
     * @param Instruction $instruction Instruction instance
     * @throws SemanticException If some semantic error occurs
     */
    public function execute(Interpreter $interpreter, Instruction $instruction): void
    {
        $interpreter->getStdErr()->writeString("\n");
        $interpreter->getStdErr()->writeString("\n");

        $interpreter->getStdErr()->writeString(ANSI_BACKGROUND_RED . ANSI_BLACK . ANSI_BOLD . str_repeat(">", 10) . " DEBUG INFORMATION START " . str_repeat("<", 10) . ANSI_CLOSE);

        $interpreter->getStdErr()->writeString("\n");
        $interpreter->getStdErr()->writeString("\n");

        $interpreter->debugger->printFrame($interpreter->globalFrame, '\/ GLOBAL FRAME \/');
        $interpreter->getStdErr()->writeString("\n");

        if (!$interpreter->localFrameStack->isEmpty()) {
            foreach ($interpreter->localFrameStack->readItems() as $index => $localFrame) {
                $humanIndex = $index + 1;
                $interpreter->debugger->printFrame($localFrame, "\/ LOCAL FRAME $humanIndex of " . $interpreter->localFrameStack->size() . " \/");
                $interpreter->getStdErr()->writeString("\n");
            }
        } else {
            $interpreter->getStdErr()->writeString(ANSI_BACKGROUND_MAGENTA . ANSI_BLACK . ANSI_BOLD . " [X]  No local frames  " . ANSI_CLOSE);
            $interpreter->getStdErr()->writeString("\n");
            $interpreter->getStdErr()->writeString("\n");
        }

        if ($interpreter->temporaryFrame) {
            $interpreter->debugger->printFrame($interpreter->temporaryFrame, '\/ TEMPORARY FRAME \/');
        } else {
            $interpreter->getStdErr()->writeString(ANSI_BACKGROUND_MAGENTA . ANSI_BLACK . ANSI_BOLD . " [X]  No temporary frame  " . ANSI_CLOSE);
        }

        $interpreter->getStdErr()->writeString("\n");

        $interpreter->debugger->printDataStack();

        $interpreter->getStdErr()->writeString("\n");

        $interpreter->debugger->printCallStack();

        $interpreter->getStdErr()->writeString("\n");

        $interpreter->debugger->printExecutionStatistics();
        $interpreter->getStdErr()->writeString("\n");

        $interpreter->getStdErr()->writeString(ANSI_BACKGROUND_MAGENTA . ANSI_BLACK . ANSI_BOLD . " > Instructions executed: " . $interpreter->executionCounter . " " . ANSI_CLOSE);
        $interpreter->getStdErr()->writeString("\n");

        $interpreter->getStdErr()->writeString(ANSI_BACKGROUND_MAGENTA . ANSI_BLACK . ANSI_BOLD . " > Current instruction counter: " . $interpreter->instructionCounter . " " . ANSI_CLOSE);
        $interpreter->getStdErr()->writeString("\n");

        $interpreter->getStdErr()->writeString("\n");
        $interpreter->getStdErr()->writeString("\n");

        $interpreter->getStdErr()->writeString(ANSI_BACKGROUND_RED . ANSI_BLACK . ANSI_BOLD . str_repeat(">", 10) . " DEBUG INFORMATION END " . str_repeat("<", 10) . ANSI_CLOSE);

        $interpreter->getStdErr()->writeString("\n");
        $interpreter->getStdErr()->writeString("\n");
    }
}