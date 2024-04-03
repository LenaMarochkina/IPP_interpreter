<?php

namespace IPP\Student;

use Exception;

/**
 * @template T
 * A generic stack class.
 */
class GenericStack
{
    /**
     * @var T[]
     */
    private array $items = [];

    /**
     * Pushes an element onto the stack.
     *
     * @param T $item The element to push.
     * @return void
     */
    public function push(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Removes and returns the element from the top of the stack.
     *
     * @return T | null The element that was popped.
     * @throws Exception if the stack is empty.
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new Exception('Stack is empty');
        }

        return array_pop($this->items);
    }

    /**
     * Checks if the stack is empty.
     *
     * @return bool True if the stack is empty, false otherwise.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

