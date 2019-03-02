<?php

namespace GMDepMan\Service;

use Assert\Assertion;
use GMDepMan\Entity\DepManEntity;

class PackageService
{
    const REPO_DIRECTORY = 'directory';
    const REPO_VCS = 'vcs';
    const REPO_GMDEPMAN = 'gmdepman';

    private $repositories = [
        [
            'type' => self::REPO_DIRECTORY,
            'uri' => 'C:\Users\PC\Documents\GameMakerStudio2\GMDepMan\tests\projects'
        ],
    ];

    private $repositories_EXAMPLE = [
        [
            'type' => self::REPO_DIRECTORY,
            'uri' => 'C:\Users\PC\Documents\GameMakerStudio2\GMDepMan\tests\projects'
        ],
        [
            'type' => self::REPO_VCS,
            'uri' => 'git@github.com:GameMakerHub/GameMakerStandards.git'
        ],
        [
            'type' => self::REPO_VCS,
            'uri' => 'https://github.com/GameMakerHub/GameMakerStandards.git'
        ],
        [
            'type' => self::REPO_GMDEPMAN,
            'uri' => 'https://raw.githubusercontent.com/GameMakerHub/packages/master/packages.json'
        ],
    ];

    public function getPackage(string $package, string $version):DepManEntity {
        throw new \InvalidArgumentException('TODO check repo for package');
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