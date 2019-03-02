<?php

namespace GMDepMan\Service;

use Assert\Assertion;
use GMDepMan\Entity\DepManEntity;

class PackageService
{
    public function getPackage(string $identifier):DepManEntity {
        return $this->getPackageByPath($identifier);
    }

    public function getPackageByPath(string $projectPath):DepManEntity {
        // Might be overkill, also in the depmanentity
        Assertion::directory($projectPath, $projectPath . ' does not exist');
        Assertion::file($projectPath . '/gmdepman.json', 'Project does not contain a gmdepman.json file');
        Assertion::file($projectPath . '/gmdepman.gdm', 'Project does not contain a gmdepman.gdm file');

        return new DepManEntity($projectPath);
    }
}