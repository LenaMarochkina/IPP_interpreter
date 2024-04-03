<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

class Variable
{
    /**
     * @var string Variable name
     */
    private string $name;
    
    /**
     * @var string|null Variable value
     */
    private string|null $value;

    /**
     * @var E_ARGUMENT_TYPE Variable type
     */
    private E_ARGUMENT_TYPE $type;

    /**
     * Variable constructor
     *
     * @param string $name Variable name
     * @param E_ARGUMENT_TYPE $type Variable type
     * @param string|null $value Variable value. Default is null
     */
    public function __construct(string $name, E_ARGUMENT_TYPE $type, string|null $value = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Get variable name
     *
     * @return string Variable name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set variable type
     *
     * @throws SemanticException If type is not a variable type
     */
    public function setType(E_ARGUMENT_TYPE $type): void
    {
        if (!$type->isVariableType()) {
            throw new SemanticException("Invalid variable type '$type->value' [$type->name]");
        }

        $this->type = $type;
    }

    /**
     * Get variable type
     *
     * @return E_ARGUMENT_TYPE Variable type
     */
    public function getType(): E_ARGUMENT_TYPE
    {
        return $this->type;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * Get variable value
     *
     * @return string|null Variable value
     */
    public function getValue(): string|null
    {
        return $this->value;
    }

    /**
     * Parse variable name to frame and name
     *
     * @param string $name Variable name
     * @return array{E_VARIABLE_FRAME, string} Frame and name of the variable
     * @throws SemanticException If frame or name is invalid
     */
    public static function parseVariableName(string $name): array
    {
        [$frame, $name] = explode('@', $name);

        if (!E_VARIABLE_FRAME::containsValue($frame)) {
            throw new SemanticException("Invalid frame '$frame'");
        }

        if (!preg_match('/^[_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*$/', $name)) {
            throw new SemanticException("Invalid variable name '$name'");
        }

        return [
            E_VARIABLE_FRAME::fromValue($frame),
            $name
        ];
    }
}
