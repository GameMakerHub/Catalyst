<?php
namespace GMDepMan\Entity;

use Assert\Assert;
use Assert\Assertion;
use Assert\AssertionFailedException;
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

    /** @var ProjectEntity */
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

    public function initialize(string $projectPath, string $name, string $description, string $license, string $homepage, string $yyp)
    {
        $this->projectPath = $projectPath;
        $this->name = $name;
        $this->description = $description;
        $this->license = $license;
        $this->homepage = $homepage;
        $this->yyp = $yyp;

        $this->depData = new \stdClass();

        $this->save();
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
        $config = json_decode($this->projectPath . '/gmdepman.json');
        if (null === $config) {
            throw new MalformedProjectFileException('gmdepman.json is malformed');
        }

        $this->yyp = $config->yyp ?? null;
        $projectFilename = $this->projectPath . '/' . $this->yyp;
        $this->projectEntity = (new ProjectEntity())->load($projectFilename);

        try {
            Assertion::file($projectFilename);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException('Project file ' . $projectFilename . ' does not exist');
        }

        $this->name = $config->name;
        $this->description = $config->description ?? null;
        $this->license = $config->license ?? null;
        $this->homepage = $config->homepage ?? null;
    }

    /**
     * @return string
     */
    public function getGdm()
    {
        return serialize($this->depData);
    }

    /**
     * @return string
     */
    public function getJson()
    {
        $jsonObj = new \stdClass();

        $jsonObj->name = $this->name;
        $jsonObj->description = $this->description;
        $jsonObj->license = $this->license;
        $jsonObj->homepage = $this->homepage;
        $jsonObj->yyp = $this->yyp;

        return json_encode($jsonObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Persist the files and write to disk
     */
    public function save()
    {
        file_put_contents($this->projectPath . '/gmdepman.json', $this->getJson());
        file_put_contents($this->projectPath . '/gmdepman.gdm', $this->getGdm());
    }
}