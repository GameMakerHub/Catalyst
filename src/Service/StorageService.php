<?php

namespace GMDepMan\Service;

use GMDepMan\Entity\ProjectEntity;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class StorageService
{
    public function loadYyp($filename) {
        if (!file_exists($filename)) {
            throw new FileNotFoundException($filename . ' not found');
        }

        $project = new ProjectEntity();
        $project->fromJson(file_get_contents($filename));
    }
}