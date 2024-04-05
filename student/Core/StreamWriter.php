<?php

namespace IPP\Student\Core;

use IPP\Student\FloatHelpers;
use IPP\Student\Value;

class StreamWriter extends \IPP\Core\StreamWriter
{
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