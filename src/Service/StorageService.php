<?php

namespace Catalyst\Service;

use Assert\Assertion;
use Catalyst\Entity\YoYoProjectEntity;

class StorageService
{
    public function fileExists(string $file):bool {
        return file_exists($file);
    }
}