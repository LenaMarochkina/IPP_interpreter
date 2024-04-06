<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;
use IPP\Student\Exception\VariableAccessException;

class Frame
{
    /**
     * @var Variable[] array of frame variables
     */
    private array $variables = [];

    /**
     * @var E_VARIABLE_FRAME frame type
     */
    private E_VARIABLE_FRAME $frame;

    public function __construct(E_VARIABLE_FRAME $frame)
    {
        $this->frame = $frame;
    }

    /**
     * Create variable
     *
     * @param string $name Variable name
     * @param E_ARGUMENT_TYPE $type Variable type
     * @param string|null $value Variable value
     * @return Variable Created variable
     * @throws SemanticException If variable already exists
     */
    public function createVariable(string $name, E_ARGUMENT_TYPE $type, string|null $value = null): Variable
    {
        if ($this->containsVariable($name)) {
            throw new SemanticException("Variable $name already exists in frame {$this->frame->value}");
        }

        $variable = new Variable($type, $value);
        $this->variables[$name] = $variable;
        return $variable;
    }

    /**
     * Get variable by name
     *
     * @param string $name Variable name
     * @return Variable Found variable
     * @throws VariableAccessException If variable does not exist
     */
    public function getVariable(string $name): Variable
    {
        if (!$this->containsVariable($name)) {
            throw new VariableAccessException("Variable $name does not exist in frame {$this->frame->value}");
        }

        return $this->variables[$name];
    }

    /**
     * Get frame variables
     *
     * @return Variable[] Array of frame variables
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Check if variable exists in frame
     *s
     * @param string $name Variable name
     * @return bool True if variable exists, false otherwise
     */
    public function containsVariable(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    /**
     * Set frame type
     *
     * @param E_VARIABLE_FRAME $frame Frame type
     */
    public function setFrame(E_VARIABLE_FRAME $frame): void
    {
        $this->frame = $frame;
    }

    /**
     * Get frame type
     *
     * @return E_VARIABLE_FRAME Frame type
     */
    public function getFrame(): E_VARIABLE_FRAME
    {
        return $this->frame;
    }
}
