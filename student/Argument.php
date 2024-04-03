<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticException;

class Argument
{
    private int $_order;
    private Value $value;
    private E_ARGUMENT_TYPE $_type;

    public function __construct(int $order, string $value, E_ARGUMENT_TYPE $type)
    {
        $this->_order = $order;
        $this->value = new Value($value);
        $this->_type = $type;
    }

    /**
     * Get argument order
     *
     * @return int Order of the argument
     */
    public function getOrder(): int
    {
        return $this->_order;
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
     * @return string Argument value
     */
    public function getStringValue(): string
    {
        return $this->value->getValue();
    }

    /**
     * Get argument typed value
     *
     * @return int|string|bool|null Typed value
     * @throws SemanticException If type is not a string, int or bool
     */
    public function getTypedValue(): int|string|bool|null
    {
        if (!$this->_type->isLiteralType()) {
            throw new SemanticException("Invalid variable type '$this->_type->value' [$this->_type->name]");
        }

        return match ($this->_type) {
            E_ARGUMENT_TYPE::INT => (int)$this->value,
            E_ARGUMENT_TYPE::STRING => $this->value,
            E_ARGUMENT_TYPE::BOOL => $this->value === 'true',
            default => null,
        };
    }

    /**
     * Get argument type
     *
     * @return E_ARGUMENT_TYPE argument type
     */
    public function getType(): E_ARGUMENT_TYPE
    {
        return $this->_type;
    }
}