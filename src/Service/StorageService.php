<?php

namespace GMDepMan\Service;

use Assert\Assertion;
use GMDepMan\Entity\ProjectEntity;

class StorageService
{
    /**
     * @param string $filename
     * @return ProjectEntity
     */
    public function loadYyp(string $filename) {
        Assertion::file($filename);

        $project = new ProjectEntity();
        $project->fromJson(file_get_contents($filename));
        return $project;
    }

    public function saveYyp(ProjectEntity $projectEntity) {
        //stub
    }
}