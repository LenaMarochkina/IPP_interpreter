<?php

namespace IPP\Student\Core;

use IPP\Core\Exception\OutputFileException;
use IPP\Student\FloatHelpers;

class StreamWriter extends \IPP\Core\StreamWriter
{
    /**
     * Write float value to the stream
     *
     * @throws OutputFileException If writing to the stream fails
     */
    public function writeFloat(float $value): void
    {
        $floatScienceValue = FloatHelpers::floatToScienceString($value);
        $floatPureValue = sprintf('%f', $value);

        // TODO: Remove when submitting final solution. Use this line for testing
        $result = fwrite($this->stream, "$floatScienceValue ($floatPureValue)");
//        $result = fwrite($this->stream, $floatScienceValue);
        $this->checkResult($result);
    }
}