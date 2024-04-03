<?php

namespace IPP\Student;

class Argument
{
    private int $_order;
    private string $_value;
    private E_ARGUMENT_TYPE $_type;

    public function __construct(int $order, string $value, E_ARGUMENT_TYPE $type)
    {
        $this->_order = $order;
        $this->_value = $value;
        $this->_type = $type;
    }

    public function getOrder(): int
    {
        return $this->_order;
    }

    public function getValue(): string
    {
        return $this->_value;
    }

    public function getType(): E_ARGUMENT_TYPE
    {
        return $this->_type;
    }
}