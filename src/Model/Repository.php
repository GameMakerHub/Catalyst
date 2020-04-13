<?php
namespace Catalyst\Model;

use Assert\Assert;
use Assert\Assertion;
use Catalyst\Entity\CatalystEntity;
use Catalyst\Exception\PackageNotFoundException;
use Catalyst\Exception\PackageNotSatisfiableException;
use Catalyst\Service\GithubService;
use Catalyst\Service\StorageService;
use Composer\Semver\Semver;

class Repository implements \JsonSerializable {

    const REPO_DIRECTORY = 'directory';
    const REPO_VCS = 'vcs';
    const REPO_CATALYST = 'catalyst';

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
        Assertion::inArray($type, $this->getRepositoryTypes());
        $this->type = $type;
        $this->uri = $uri;
    }

    public function setAvailablePackages($data)
    {
        $this->scannedPackages = true;
        $this->availablePackages = $data;
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
        $satisfied = Semver::satisfiedBy(array_keys($this->availablePackages[$packageName]['versions']), $version);
        if (count($satisfied)) {
            return Semver::rsort($satisfied);
        }
        throw new PackageNotFoundException($packageName, $version);
    }

    public function packageExists(string $packageName, string $version): bool
    {
        $satisfieableVersions = $this->getSatisfiableVersions($packageName, $version);
        if (count($satisfieableVersions)) {
            return true;
        }

        return false;
    }

    public function findPackage(string $packageName, string $version): CatalystEntity
    {
        $satisfieableVersions = $this->getSatisfiableVersions($packageName, $version);
        if (count($satisfieableVersions)) {
            switch ($this->type) {
                case self::REPO_DIRECTORY:
                    return CatalystEntity::createFromPath($this->availablePackages[$packageName]['source'], true);
                    break;
                case self::REPO_VCS: //@todo replace zipball downloading with git clone
                    //@see https://github.com/GameMakerHub/Catalyst/issues/11
                    $zipBallUrl = $this->availablePackages[$packageName][$satisfieableVersions[0]];
                    $githubService = new GithubService();
                    return CatalystEntity::createFromPath($githubService->getDownloadedPackageFolder($zipBallUrl), true);
                    break;
                case self::REPO_CATALYST: //@todo replace zipball downloading with git clone
                    //@see https://github.com/GameMakerHub/Catalyst/issues/11
                    $githubService = new GithubService();
                    $zipBallUrl = $githubService->getZipballUrl($this->availablePackages[$packageName]['source'], $satisfieableVersions[0]);
                    return CatalystEntity::createFromPath($githubService->getDownloadedPackageFolder($zipBallUrl), true);
                    break;
            }
        }

        throw new PackageNotSatisfiableException($packageName, $version);
    }

    public function findPackageDependencies(string $packageName, string $version): array
    {
        $satisfieableVersions = $this->getSatisfiableVersions($packageName, $version);
        if (count($satisfieableVersions)) {
            switch ($this->type) {
                case self::REPO_VCS:
                    $githubService = new GithubService();
                    return $githubService->getDependenciesFor($packageName, $satisfieableVersions[0]);
                    break;
                case self::REPO_CATALYST:
                case self::REPO_DIRECTORY:
                    return $this->availablePackages[$packageName]['versions'][$version];
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
            case self::REPO_CATALYST:
                $this->scanCatalystForPackages();
                break;
        }
    }

    private function scanPackagesForDirectory():void
    {
        $packagePaths = [];

        $realLocation = StorageService::pathToAbsolute($this->uri);
        foreach (glob($realLocation . '/*', GLOB_ONLYDIR) as $projectPath) {
            if (file_exists($projectPath . '/catalyst.json')) {
                $packagePaths[] = $projectPath;
            }
        }

        foreach ($packagePaths as $packagePath) {
            try {
                $jsonData = json_decode(file_get_contents($packagePath . '/catalyst.json'));
                if ($jsonData->name) {
                    if (!array_key_exists($jsonData->name, $this->availablePackages)) {
                        $this->availablePackages[$jsonData->name] = [];
                    }
                    $this->availablePackages[$jsonData->name]['source'] = $packagePath;
                    $versions = [];
                    if (isset($jsonData->require)) {
                        $versions = (array) $jsonData->require;
                    }
                    $this->availablePackages[$jsonData->name]['versions'] = [
                        '1.0.0' => $versions //Default version, 1.0.0 ?
                    ];
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
    }

    private function scanGithubForPackages(): void
    {
        throw new \Exception('Using github as a repository is not yet supported');

        $githubService = new GithubService();

        $packageName = $githubService->getPackageNameFromUrl($this->uri);
        $this->availablePackages[$packageName] = $githubService->getTags($packageName);
    }

    private function scanCatalystForPackages(): void
    {
        if (($GLOBALS['repocache'][$this->uri] ?? null) == null) {
            $httpClient = new \GuzzleHttp\Client([
                'base_uri' => $this->uri . '/',
                'headers' => [
                    'User-Agent' => 'catalyst/1.0',
                    'Accept'     => 'application/vnd.catalyst.v1+json',
                ]
            ]);

            try {
                $fetched = $httpClient->get('packages');
                $packages = json_decode($fetched->getBody()->getContents(), true)['packages'];
            } catch (\Exception $e) {
                throw new \Exception('The repository "' . $this->uri . '" seems to be unavailable right now: ' . $e->getMessage());
            }

            if (!$packages) {
                throw new \Exception('The repository "' . $this->uri . '" seems to be unavailable right now.');
            }

            $GLOBALS['repocache'][$this->uri] = $packages;
        }

        foreach ($GLOBALS['repocache'][$this->uri] as $package) {
            $versions = [];
            foreach ($package['versions'] as $data) {
                $versions[$data['version']] = $data['dependencies'];
            }

            $this->availablePackages[$package['name']] = [
                'source' => $package['source'],
                'versions' => $versions
            ];
        }
    }

    private function getRepositoryTypes(): array
    {
        return [
            self::REPO_CATALYST,
            self::REPO_DIRECTORY,
            self::REPO_VCS,
        ];
    }
}