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

        $satisfied = Semver::satisfiedBy(array_keys($this->availablePackages[$packageName]['versions']), $version);
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
                    $githubService = new GithubService();
                    $zipBallUrl = $githubService->getZipballUrl($this->availablePackages[$packageName]['source'], $satisfieableVersions[0]);
                    return new DepManEntity($githubService->getDownloadedPackageFolder($zipBallUrl));
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
            case self::REPO_GMDEPMAN:
                $this->scanGmdepmanForPackages();
                break;
        }
    }

    private function scanPackagesForDirectory():void
    {
        throw new \Exception('Redo in new structure');
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
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
    }

    private function scanGithubForPackages(): void
    {
        throw new \Exception('Redo in new structure');

        $githubService = new GithubService();

        $packageName = $githubService->getPackageNameFromUrl($this->uri);
        $this->availablePackages[$packageName] = $githubService->getTags($packageName);
    }

    private function scanGmdepmanForPackages(): void
    {
        $httpClient = new \GuzzleHttp\Client([
            'base_uri' => $this->uri . '/',
            'headers' => [
                'User-Agent' => 'gmdepman/1.0',
                'Accept'     => 'application/vnd.gmdepman.v1+json',
            ]
        ]);

        $packages = json_decode($httpClient->get('packages')->getBody()->getContents(), true)['packages'];

        foreach ($packages as $package) {

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
}