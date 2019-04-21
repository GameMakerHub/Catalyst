<?php

namespace GMDepMan\Service;

use Assert\Assertion;
use GMDepMan\Entity\DepManEntity;
use GMDepMan\Exception\PackageNotFoundException;
use GMDepMan\Exception\PackageNotSatisfiableException;
use GMDepMan\Model\Repository;

class PackageService
{
    /**
     * @return Repository[]
     */
    public function getDefaultRepositories():array
    {
        return [
            //new Repository(Repository::REPO_DIRECTORY, 'C:\Users\PC\Documents\GameMakerStudio2\GMDepMan\tests')
            //new Repository(Repository::REPO_GMDEPMAN, 'https://raw.githubusercontent.com/GameMakerHub/packages/master/packages.json')
            //new Repository(Repository::REPO_VCS, 'git@github.com:DukeSoft/extended-functions.git')
            new Repository(Repository::REPO_GMDEPMAN, 'http://repo.gamemakerhub.net')
        ];
    }

    /**
     * @param string $package
     * @param string $version
     * @param array|null $repositoriesOverride
     * @return DepManEntity
     */
    public function getPackage(string $package, string $version, array $repositoriesOverride = []):DepManEntity {

        $repositories = $repositoriesOverride + $this->getDefaultRepositories();

        foreach ($repositories as $repository) {
            try {
                return $repository->findPackage($package, $version);
            } catch (PackageNotFoundException $e) {
            }
        }

        throw new PackageNotFoundException($package, $version);
    }

    public function getPackageDependencies(string $package, string $version, array $repositoriesOverride = [])
    {
        $repositories = $repositoriesOverride + $this->getDefaultRepositories();

        foreach ($repositories as $repository) {
            try {
                return $repository->findPackageDependencies($package, $version);
            } catch (PackageNotFoundException $e) {
            }
        }

        throw new PackageNotFoundException($package, $version);
    }

    /**
     * @param string $package
     * @param string $version
     * @param array|null $repositoriesOverride
     * @return array
     */
    public function getSatisfiableVersions(string $package, string $version, array $repositoriesOverride = []):array {

        $repositories = $repositoriesOverride + $this->getDefaultRepositories();
        $versions = [];

        foreach ($repositories as $repository) {
            $versions += $repository->getSatisfiableVersions($package, $version);
        }

        return $versions;
    }

    public function getPackageByPath(string $projectPath):DepManEntity {
        // Might be overkill, also in the depmanentity
        Assertion::directory($projectPath, $projectPath . ' does not exist');
        Assertion::file($projectPath . '/gmdepman.json', 'Project does not contain a gmdepman.json file');

        return new DepManEntity($projectPath);
    }
}