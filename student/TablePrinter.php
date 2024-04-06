<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;


class TablePrinter
{
    /**
     * @var OutputWriter Output stream
     */
    private OutputWriter $output;

    /**
     * Table columns in format [column_name => column_title]
     * @var array<string> Columns
     */
    private array $columns;

    /**
     * Table rows in format [row_id => [column_name => value, ...]]
     * @var array<string,array<string, string>> Rows
     */
    private array $rows = [];

    /**
     * Horizontal padding for columns
     * @var int Padding
     */
    private int $padding = 2;

    private string $caption = '';

    public string $captionStyle = ANSI_BACKGROUND_MAGENTA . ANSI_BLACK;
    public string $headerStyle = ANSI_BACKGROUND_CYAN . ANSI_BLACK;
    public string $bodyStyle = ANSI_BACKGROUND_BLUE . ANSI_BLACK;

    /**
     * @param array<string, string> $columns Table columns in format [column_name => column_title]
     */
    public function __construct(OutputWriter $output, array $columns = [])
    {
        $this->output = $output;
        $this->columns = $columns;
    }

    /**
     * Add row to table
     *
     * @param string $id Row ID
     * @param array<string, string> $row Row data in format [column_name => value, ...]
     */
    public function addRow(string $id, array $row): void
    {
        $this->rows[$id] = $row;
    }

    /**
     * Set table caption
     *
     * @param string $caption Table caption
     */
    public function setCaption(string $caption): void
    {
        $this->caption = $caption;
    }

    /**
     * Get table width for each column
     *
     * @return array<string, int> Column widths
     */
    private function getTableWidth(): array
    {
        if (empty($this->rows)) {
            return array_combine(
                array_keys($this->columns),
                array_map(fn($title) => strlen($title), $this->columns)
            );
        }

        $widths = array_combine(
            array_keys($this->columns),
            array_map(
                fn($key) => max(array_map(fn($row) => isset($row[$key]) ? strlen($row[$key]) : 0, $this->rows)),
                array_keys($this->columns),
                array_values($this->columns)
            )
        );

        return array_combine(
            array_keys($this->columns),
            array_map(
                fn($key) => max($widths[$key], strlen($this->columns[$key])),
                array_keys($this->columns),
                array_values($this->columns)
            )
        );
    }

    /**
     * Get total width of the table
     *
     * @return int Total width
     */
    private function getTotalWidth(): int
    {
        $widths = $this->getTableWidth();

        return array_sum($widths) // Total width of all columns
            + count($widths) * $this->padding * 2 // Each column has padding on both sides
            + count($widths) - 1 // Each column has a separator
            + 2; // First and last column have a separator
    }

    /**
     * Print table to output
     */
    public function printTable(): void
    {
        $this->printHeader();

        foreach ($this->rows as $row) {
            $this->printRow($row);
        }

        $totalWidth = $this->getTotalWidth();

        $this->output->writeString($this->bodyStyle);
        $this->output->writeString(str_repeat('-', $totalWidth));
        $this->output->writeString(ANSI_CLOSE);
        $this->output->writeString("\n");
    }

    /**
     * Print table header
     */
    private function printHeader(): void
    {
        $widths = $this->getTableWidth();
        $totalWidth = $this->getTotalWidth();

        $this->output->writeString($this->captionStyle);

        if (!empty($this->caption)) {
            $this->output->writeString(ANSI_BOLD . str_pad($this->caption, $totalWidth, " ", STR_PAD_BOTH) . ANSI_CLOSE . $this->captionStyle);
            $this->output->writeString(ANSI_CLOSE);
            $this->output->writeString("\n");
        }

        $this->output->writeString($this->headerStyle);
        $this->output->writeString(str_repeat('-', $totalWidth));
        $this->output->writeString(ANSI_CLOSE);
        $this->output->writeString("\n");

        $this->output->writeString($this->headerStyle);
        $this->output->writeString('|');
        foreach ($this->columns as $name => $title) {
            $this->output->writeString(ANSI_BOLD . str_pad($title, $widths[$name] + $this->padding * 2, " ", STR_PAD_BOTH) . ANSI_CLOSE . $this->headerStyle);
            $this->output->writeString('|');
        }
        $this->output->writeString(ANSI_CLOSE);
        $this->output->writeString("\n");
        $this->output->writeString($this->headerStyle);
        $this->output->writeString(str_repeat('-', $totalWidth));
        $this->output->writeString(ANSI_CLOSE);
        $this->output->writeString("\n");

        $this->output->writeString(ANSI_CLOSE);
    }

    /**
     * Print table row
     *
     * @param array<string, string> $row Row data
     */
    private function printRow(array $row): void
    {
        $widths = $this->getTableWidth();

        $this->output->writeString($this->bodyStyle);
        $this->output->writeString('|');
        foreach ($this->columns as $name => $title) {
            $this->output->writeString(str_pad($row[$name] ?? '', $widths[$name] + $this->padding * 2, " ", STR_PAD_BOTH));
            $this->output->writeString('|');
        }
        $this->output->writeString(ANSI_CLOSE);
        $this->output->writeString("\n");
    }
}