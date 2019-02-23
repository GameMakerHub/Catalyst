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

    public function __construct(string $projectPath)
    {
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
     * Load a JSON string in YYP format
     * @param string $json
     */
    public function load()
    {
        Assertion::file($yypFilename);

        $this->originalYyp = json_decode(file_get_contents($this->yypFilename));

        try {
            Assertion::notNull($this->originalYyp);
        } catch (\InvalidArgumentException $e) {
            throw new MalformedProjectFileException($yypFilename . ' is not a valid GMS2 .yyp file');
        }

        $this->yypFilename = $yypFilename;
        $this->loadDepMan();
    }

    private function getDepManFileName()
    {
        return dirname($this->yypFilename) . 'gmdepman.json';
    }

    private function loadDepMan()
    {
        try {
            Assertion::file($this->getDepManFileName());
        } catch (\InvalidArgumentException $e) {

        }
    }

    /**
     * Return this project in YYP format
     */
    public function getYypJson()
    {
        return 0;
    }

    /**
     * Persist the files and write to disk
     */
    public function save()
    {
        //stub
    }
}