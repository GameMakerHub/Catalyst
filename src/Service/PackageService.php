<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Exception\PackageNotFoundException;
use Catalyst\Model\Repository;
use Composer\Semver\Semver;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class PackageService
{
    /** @var Repository[] */
    private $repositories;

    public function __construct()
    {
        // Add default repositories
        //new Repository(Repository::REPO_DIRECTORY, 'C:\Users\PC\Documents\GameMakerStudio2\Catalyst\tests')
        //new Repository(Repository::REPO_catalyst, 'https://raw.githubusercontent.com/GameMakerHub/packages/master/packages.json')
        //new Repository(Repository::REPO_VCS, 'git@github.com:DukeSoft/extended-functions.git')
        $this->addRepository(new Repository(Repository::REPO_CATALYST, 'http://repo.gamemakerhub.net'));
    }

    public function clearRepositories(): void
    {
        $this->repositories = [];
    }

    public function addRepository(Repository $repository): void
    {
        $this->repositories[] = $repository;
    }

    public function getPackage(string $package, string $version): CatalystEntity
    {
        foreach ($this->repositories as $repository) {
            try {
                return $repository->findPackage($package, $version);
            } catch (PackageNotFoundException $e) {
            }
        }

        throw new PackageNotFoundException($package, $version);
    }

    public function packageExists(string $package, string $version): bool
    {
        foreach ($this->repositories as $repository) {
            try {
                return $repository->packageExists($package, $version);
            } catch (PackageNotFoundException $e) {
            }
        }

        throw new PackageNotFoundException($package, $version);
    }

    public function getPackageDependencies(string $package, string $version): array
    {
        foreach ($this->repositories as $repository) {
            try {
                return $repository->findPackageDependencies($package, $version);
            } catch (PackageNotFoundException $e) {
            }
        }

        throw new PackageNotFoundException($package, $version);
    }

    public function getSatisfiableVersions(string $package, string $version): array
    {
        $versions = [];

        foreach ($this->repositories as $repository) {
            try {
                $versions += $repository->getSatisfiableVersions($package, $version);
            } catch (PackageNotFoundException $e) {
                // Ignore
            }
        }

        return $versions;
    }

    private function addRepositoriesFromCatalyst(CatalystEntity $project)
    {
        foreach ($project->repositories() as $type => $location) {
            $this->addRepository(new Repository($type, $location));
        }
    }

    public function solveDependencies(CatalystEntity $project, $finalPackages = [])
    {
        $requirements = $project->require();
        $this->addRepositoriesFromCatalyst($project);

        // First find all available versions of all required packages
        foreach ($requirements as $package => $version) {
            $finalPackages[$package] = $this->getSatisfiableVersions($package, $version);
            if (count($finalPackages[$package]) == 0) {
                throw new UnsatisfiedDependencyException(
                    sprintf('No version for constraint "%s" for package "%s" can be found', $version, $package)
                );
            }
        }

        // Now find the depdencies' dependencies recursively
        $addedNewPackage = true;
        while ($addedNewPackage) {
            $addedNewPackage = false;
            foreach ($finalPackages as $package => $versions) {
                if (count($versions) == 0) {
                    throw new UnsatisfiedDependencyException(
                        $package . ' cant be satisfied, due to a dependency constraint'
                    );
                }
                $testVersion = 0;
                $deps = $this->getPackageDependencies($package, $versions[$testVersion]);
                foreach ($deps as $depPackage => $depVersionConstraint) {
                    if (array_key_exists($depPackage, $finalPackages)) {
                        //Apply constraint on current list
                        $finalPackages[$depPackage] = Semver::satisfiedBy($finalPackages[$depPackage], $depVersionConstraint);
                    } else {
                        //Add new pacakge to list
                        $finalPackages[$depPackage] = $this->getSatisfiableVersions($depPackage, $depVersionConstraint);
                        $addedNewPackage = true;
                    }
                }
            }
        }

        // Now pick the latest available version
        $result = [];
        foreach ($finalPackages as $package => $versions) {
            if (count($versions) == 0) {
                throw new UnsatisfiedDependencyException(
                    $package . ' cant be satisfied, due to a dependency constraint'
                );
            }
            $result[$package] = $versions[0];
        }

        return $result;
    }
}