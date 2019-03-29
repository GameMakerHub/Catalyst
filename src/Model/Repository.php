<?php
namespace GMDepMan\Model;

use Composer\Semver\Semver;
use GMDepMan\Entity\DepManEntity;
use GMDepMan\Exception\PackageNotFoundException;
use GMDepMan\Exception\PackageNotSatisfiableException;
use GMDepMan\Service\GithubService;

class Repository implements \JsonSerializable {

    const REPO_DIRECTORY = 'directory';
    const REPO_VCS = 'vcs';
    const REPO_GMDEPMAN = 'gmdepman';

    /** @var string */
    public $type;

    /** @var string */
    public $uri;

    /** @var array */
    private $availablePackages = [];

    /** @var bool */
    private $scannedPackages = false;

    public function __construct(string $type, string $uri)
    {
        $this->type = $type;
        $this->uri = $uri;
    }

    public function jsonSerialize()
    {
        return $this;
    }

    public function getSatisfiableVersions(string $packageName, string $version):array
    {
        $this->scanPackagesIfNotScanned();

        if (!array_key_exists($packageName, $this->availablePackages)) {
            throw new PackageNotFoundException($packageName, $version);
        }

        $satisfied = Semver::satisfiedBy(array_keys($this->availablePackages[$packageName]), $version);
        if (count($satisfied)) {
            return Semver::rsort($satisfied);
        }
        throw new PackageNotFoundException($packageName, $version);
    }

    public function findPackage(string $packageName, string $version):DepManEntity
    {
        $satisfieableVersions = $this->getSatisfiableVersions($packageName, $version);
        if (count($satisfieableVersions)) {

            switch ($this->type) {
                case self::REPO_DIRECTORY:
                    return new DepManEntity($this->availablePackages[$packageName][$satisfieableVersions[0]]);
                    break;
                case self::REPO_VCS:
                    $zipBallUrl = $this->availablePackages[$packageName][$satisfieableVersions[0]];
                    $githubService = new GithubService();
                    return new DepManEntity($githubService->getDownloadedPackageFolder($zipBallUrl));
                    break;
                case self::REPO_GMDEPMAN:
                    throw new \RuntimeException('Repo type not supported yet');
                    break;
            }
        }

        throw new PackageNotSatisfiableException($packageName, $version);
    }

    public function findPackageDependencies(string $packageName, string $version):array
    {
        $satisfieableVersions = $this->getSatisfiableVersions($packageName, $version);
        if (count($satisfieableVersions)) {
            switch ($this->type) {
                case self::REPO_DIRECTORY:
                    $depManEntity = new DepManEntity($this->availablePackages[$packageName][$satisfieableVersions[0]]);
                    return $depManEntity->require;
                    break;
                case self::REPO_VCS:
                    $githubService = new GithubService();
                    return $githubService->getDependenciesFor($packageName, $satisfieableVersions[0]);
                    break;
                case self::REPO_GMDEPMAN:
                    throw new \RuntimeException('Repo type not supported yet');
                    break;
            }
        }

        throw new PackageNotSatisfiableException($packageName, $version);
    }

    private function scanPackagesIfNotScanned():void
    {
        if ($this->scannedPackages) {
            return;
        }

        switch ($this->type) {
            case self::REPO_DIRECTORY:
                $this->scanPackagesForDirectory();
                break;
            case self::REPO_VCS:
                $this->scanGithubForPackages();
                break;
            case self::REPO_GMDEPMAN:
                throw new \RuntimeException('Repo type not supported yet');
                break;
        }
    }

    private function scanPackagesForDirectory():void
    {
        $packagePaths = [];
        foreach (glob($this->uri . '/*/*', GLOB_ONLYDIR) as $projectPath) {
            if (file_exists($projectPath . '/gmdepman.json')) {
                $packagePaths[] = $projectPath;
            }
        }

        foreach ($packagePaths as $packagePath) {
            try {
                $jsonData = json_decode(file_get_contents($packagePath . '/gmdepman.json'));
                if ($jsonData->name && $jsonData->version) {
                    if (!array_key_exists($jsonData->name, $this->availablePackages)) {
                        $this->availablePackages[$jsonData->name] = [];
                    }

                    $this->availablePackages[$jsonData->name][$jsonData->version] = $packagePath;
                    /*
                        $this->availablePackages[$jsonData->name]['1.1.0'] = $packagePath;
                        $this->availablePackages[$jsonData->name]['dev-master'] = $packagePath;
                        $this->availablePackages[$jsonData->name]['1.0.3'] = $packagePath;
                        $this->availablePackages[$jsonData->name]['1.1.1'] = $packagePath;
                        $this->availablePackages[$jsonData->name]['1.0.3-rc2'] = $packagePath;
                    */
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
    }

    private function scanGithubForPackages(): void
    {
        $matches = [];
        preg_match(
            '~git@github\.com:([a-zA-Z0-9-]+\/[a-zA-Z0-9-]+){1}\.git~',
            $this->uri,
            $matches
        );

        if (count($matches) != 2) {
            throw new \RuntimeException(
                sprintf(
                    'VCS URI "%s" is not supported - must be "%s" format',
                    $this->uri,
                    'git@github.com:vendor/package.git'
                )
            );
        }

        $githubService = new GithubService();

        $packageName = strtolower($matches[1]);
        $this->availablePackages[$packageName] = $githubService->getTags($packageName);
    }
}