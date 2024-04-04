<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

class Variable
{
    /**
     * @var Value Variable value
     */
    private Value $value;

    /**
     * @var E_ARGUMENT_TYPE Variable type
     */
    private E_ARGUMENT_TYPE $type;

    /** @var bool Is variable defined */
    private bool $defined = false;

    /**
     * Variable constructor
     *
     * @param E_ARGUMENT_TYPE $type Variable type
     * @param string|null $value Variable value. Default is null
     */
    public function __construct(E_ARGUMENT_TYPE $type, string|null $value = null)
    {
        $this->type = $type;
        $this->value = new Value($value);
    }

    /**
     * Set variable defined
     *
     * @param bool $defined Variable defined
     */
    public function setDefined(bool $defined): void
    {
        $this->defined = $defined;
    }

    /**
     * Check if variable is defined
     *
     * @return bool True if variable is defined, false otherwise
     */
    public function isDefined(): bool
    {
        return $this->defined;
    }

    /**
     * Set variable type
     *
     * @throws SemanticException If type is not a variable type
     */
    public function setType(E_ARGUMENT_TYPE $type): void
    {
        if (!$type->isLiteralType()) {
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

    /**
     * Set variable value
     *
     * @param string $value Variable value
     */
    public function setValue(string $value): void
    {
        $this->value = new Value($value);
        $this->defined = true;
    }

    /**
     * Return variable value based on type
     *
     * @return string|int|bool|null Typed value
     * @throws SemanticException If type is not a string, int or bool
     */
    public function getTypedValue(): string|int|bool|null
    {
        return $this->value->getTypedValue($this->type);
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
