<?php
namespace GMDepMan\Model;

use Composer\Semver\Semver;
use GMDepMan\Entity\DepManEntity;
use GMDepMan\Exception\PackageNotFoundException;

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

    public function findPackage(string $packageName, string $version):DepManEntity
    {
        $this->scanPackagesIfNotScanned();

        if (!array_key_exists($packageName, $this->availablePackages)) {
            throw new PackageNotFoundException($packageName, $version);
        }

        $satisfied = Semver::satisfiedBy(array_keys($this->availablePackages[$packageName]), $version);
        if (count($satisfied)) {
            $sorted = Semver::rsort($satisfied);
            return new DepManEntity($this->availablePackages[$packageName][$sorted[0]]);
        }

        throw new PackageNotFoundException($packageName, $version);
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
            case self::REPO_GMDEPMAN:
            case self::REPO_VCS:
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
                    $this->availablePackages[$jsonData->name]['1.1.0'] = $packagePath;
                    $this->availablePackages[$jsonData->name]['dev-master'] = $packagePath;
                    $this->availablePackages[$jsonData->name]['1.0.3'] = $packagePath;
                    $this->availablePackages[$jsonData->name]['1.1.1'] = $packagePath;
                    $this->availablePackages[$jsonData->name]['1.0.3-rc2'] = $packagePath;

                }
            } catch (\Exception $e) {
                // ignore
            }
        }
    }
}