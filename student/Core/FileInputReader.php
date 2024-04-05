<?php

namespace IPP\Student\Core;

class FileInputReader extends \IPP\Core\FileInputReader
{
    public function readFloat(): ?float
    {
        $result = $this->readString();
        $matches = [];

        if (is_null($result)) return null;

        // Input format: 0x123ABCp+N
        if (!preg_match('/^0x([\dA-Fa-f]*\.[\dA-Fa-f]*)p([+-]\d*)$/m', $result, $matches)) {
            return null;
        }


        return null;
    }
}