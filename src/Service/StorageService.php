<?php

namespace GMDepMan\Service;

use Assert\Assertion;
use GMDepMan\Entity\YoYoProjectEntity;

class StorageService
{
    /**
     * @param string $filename
     * @return YoYoProjectEntity
     */
    public function loadYyp(string $filename) {
        Assertion::file($filename);

        $project = new YoYoProjectEntity();
        $project->fromJson(file_get_contents($filename));
        return $project;
    }

    public function saveYyp(YoYoProjectEntity $projectEntity) {
        //stub
    }
}