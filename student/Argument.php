<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

class Argument
{
    private Value $value;
    private E_ARGUMENT_TYPE $type;

    public function __construct(string|null $value, E_ARGUMENT_TYPE $type)
    {
        $this->value = new Value($value);
        $this->type = $type;
    }

    /**
     * Get argument string value
     *
     * @return Value Argument value
     */
    public function getValue(): Value
    {
        return $this->value;
    }

    /**
     * Get argument string value
     *
     * @return string|null Argument value
     */
    public function getStringValue(): string|null
    {
        return $this->value->getValue();
    }

    /**
     * Get argument typed value
     *
     * @return float|int|string|bool|null Typed value
     * @throws SemanticException If type is not a string, int or bool
     */
    public function getTypedValue(): float|int|string|bool|null
    {
        return $this->value->getTypedValue($this->type);
    }

    /**
     * Get argument type
     *
     * @return E_ARGUMENT_TYPE argument type
     */
    public function getType(): E_ARGUMENT_TYPE
    {
        return $this->type;
    }
}