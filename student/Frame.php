<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

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
     * @param string|null $value Variable value
     * @return Variable Created variable
     */
    public function createVariable(string $name, string|null $value = null): Variable
    {
        $variable = new Variable($name, $value, $this->frame);
        $this->variables[$name] = $variable;
        return $variable;
    }

    /**
     * Get variable by name
     *
     * @param string $name Variable name
     * @return Variable Found variable
     * @throws SemanticException If variable does not exist
     */
    public function getVariable(string $name): Variable
    {
        if (!array_key_exists($name, $this->variables)) {
            throw new SemanticException("Variable $name does not exist in frame " . $this->frame->value);
        }
        return $this->variables[$name];
    }
}
