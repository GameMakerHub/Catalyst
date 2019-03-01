<?php

namespace GMDepMan\Service;

use Assert\Assertion;
use GMDepMan\Entity\YoYoProjectEntity;

class StorageService
{
    public function fileExists(string $file):bool {
        return file_exists($file);
    }
}