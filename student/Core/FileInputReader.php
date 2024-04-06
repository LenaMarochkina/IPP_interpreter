<?php

namespace IPP\Student\Core;

use IPP\Student\FloatHelpers;

class FileInputReader extends \IPP\Core\FileInputReader
{
    public function readFloat(): ?float
    {
        $result = $this->readString();

        if (is_null($result)) return null;

        return FloatHelpers::parseInputFloat($result);
    }
}