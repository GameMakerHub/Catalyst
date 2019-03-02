<?php
namespace GMDepMan\Entity;

use Assert\Assert;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use GMDepMan\Exception\MalformedProjectFileException;
use Symfony\Component\Console\Output\Output;

class DepManEntity {

    const ALLOWED_LICENSES = [
        'MIT',
        'Apache-2.0',
        'BSD-2-Clause',
        'BSD-3-Clause',
        'BSD-4-Clause',
        'GPL-2.0-only', 'GPL-2.0-or-later',
        'GPL-3.0-only', 'GPL-3.0-or-later',
        'LGPL-2.1-only', 'LGPL-2.1-or-later',
        'LGPL-3.0-only', 'LGPL-3.0-or-later',
        'proprietary'
    ];

    /** @var YoYoProjectEntity */
    private $projectEntity;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $license;

    /** @var string */
    private $homepage;

    /** @var string */
    private $yyp;

    /** @var \stdClass */
    private $depData;

    /** @var string */
    private $projectPath;

    /** @var string */
    private $version;

    public function initialize(string $projectPath, string $name, string $description, string $license, string $homepage, string $yyp)
    {
        $this->projectPath = $projectPath;
        $this->name = $name;
        $this->description = $description;
        $this->license = $license;
        $this->homepage = $homepage;
        $this->yyp = $yyp;
        $this->version = '0.0.1';
        $this->require = [];

        $this->depData = new \stdClass();

        $this->save();
    }

    public function hasPackage(string $packageName):bool {
        return array_key_exists($packageName, $this->require);
    }

    public function require(string $package, string $version) {
        $this->require[$package] = $version;
    }

    /**
     * DepManEntity constructor.
     * @param string|false $projectPath
     */
    public function __construct($projectPath)
    {
        if (false === $projectPath) {
            return;
        }

        Assertion::directory($projectPath);
        $this->projectPath = $projectPath;

        try {
            Assertion::file($this->projectPath . '/gmdepman.gdm');
            Assertion::file($this->projectPath . '/gmdepman.json');
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException('gmdepman files are not found. Initialize first!');
        }

        $this->depData = unserialize(file_get_contents($this->projectPath . '/gmdepman.gdm'));

        if (false === $this->depData) {
            throw new MalformedProjectFileException('gmdepman.gmd is malformed');
        }

        // Load config from file
        $config = json_decode(file_get_contents($this->projectPath . '/gmdepman.json'));
        if (null === $config) {
            throw new MalformedProjectFileException('gmdepman.json is malformed');
        }

        $this->yyp = $config->yyp ?? null;
        $this->projectEntity = (new YoYoProjectEntity())->load($this);

        $this->name = $config->name;
        if (empty($this->name)) { throw new MalformedProjectFileException('gmdepman.json missing name'); }
        $this->description = $config->description ?? null;
        $this->license = $config->license ?? null;
        $this->homepage = $config->homepage ?? null;
        $this->version = $config->version ?? null;
        $this->version = $config->version ?? null;
        $this->require = (array) $config->require ?? [];
        if (empty($this->version)) { throw new MalformedProjectFileException('gmdepman.json missing version'); }
    }

    public function projectEntity():YoYoProjectEntity
    {
        return $this->projectEntity;
    }

    public function getGdm():string
    {
        return serialize($this->depData);
    }

    public function getJson():string
    {
        $jsonObj = new \stdClass();

        $jsonObj->name = $this->name;
        $jsonObj->description = $this->description;
        $jsonObj->license = $this->license;
        $jsonObj->homepage = $this->homepage;
        $jsonObj->yyp = $this->yyp;
        $jsonObj->version = $this->version;
        if (count($this->require)) { $jsonObj->require = $this->require; }

        return json_encode($jsonObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getProjectPath():string
    {
        return realpath($this->projectPath);
    }

    public function getYypFilename():string
    {
        return realpath($this->projectPath) . '/' . $this->yyp;
    }

    /**
     * Persist the files and write to disk
     */
    public function save()
    {
        file_put_contents($this->projectPath . '/gmdepman.json', $this->getJson());
        file_put_contents($this->projectPath . '/gmdepman.gdm', $this->getGdm());
    }

    public function version()
    {
        return $this->version;
    }

    public function name()
    {
        return $this->name;
    }
}