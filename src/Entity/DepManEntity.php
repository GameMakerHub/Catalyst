<?php
namespace GMDepMan\Entity;

use Assert\Assert;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use GMDepMan\Exception\MalformedProjectFileException;
use GMDepMan\Model\YoYo\Resource\GM\GMFolder;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class DepManEntity {

    const UUID_NS = '00000000-1337-fafa-0000-dededededede';

    private static $rootFolderCopyOnly = [
        'sprites',
        'tilesets',
        'sounds',
        'paths',
        'scripts',
        'shaders',
        'fonts',
        'timelines',
        'objects',
        'rooms',
        'notes',
        'datafiles'
    ];

    private static $vendorFolderName = 'vendor';

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

    /** @var array */
    private $repositories = [];

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
        $this->repositories = [];

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
        $this->require = (array) ($config->require ?? []);

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

    public function installPackage(DepManEntity $newPackage, OutputInterface $output)
    {
        //First make vendor folder for this package
        if (!$GLOBALS['dry']) {
            @mkdir($this->getProjectPath() . '/'.self::$vendorFolderName.'/' . $newPackage->name(), 0777, true);
        }

        // Loop through all files and copy / add them to this project
        $this->loopIn($output, $newPackage, $newPackage->projectEntity()->getChildren(),0);
    }

    /**
     * @param OutputInterface $output
     * @param DepManEntity $newPackage
     * @param \GMDepMan\Model\YoYo\Resource\GM\GMResource[] $children
     * @param int $level
     * @param GMFolder|null $rootFolder
     * @throws \Exception
     */
    private function loopIn(OutputInterface $output, DepManEntity $newPackage, array $children, $level = 0, GMFolder $rootFolder = null) {
        foreach ($children as $child) {
            $name = '?';

            $isFolder = false;
            if (isset($child->folderName)) {
                $name = $child->folderName;
                $isFolder = true;
                if ($name == self::$vendorFolderName || ($level == 0 && !in_array($name, self::$rootFolderCopyOnly) )) {
                    continue;
                }
            } else if (isset($child->name)) {
                $name = $child->name;
            }

            $hasChildren = count($child->getChildren()) >= 1;

            if ($isFolder && $hasChildren) {
                if ($level == 0) {
                    $rootFolder = $this->projectEntity()->createGmFolder($name . '/vendor/' . $newPackage->name());
                } else {
                    $rootFolder = $this->projectEntity()->createGmFolder($rootFolder->getFullName() . '/' . $name);
                }
                $output->writeln('    '. str_repeat('|  ', $level).'\__ <fg=cyan>' . $name . '</>['.$child->id.','.$child->getYypResource()->key().']');
                $this->loopIn($output, $newPackage, $child->getChildren(), $level+1, $rootFolder);
            }

            if (!$isFolder) {
                $output->writeln('    ' . str_repeat('|  ', $level).'\__ <fg=green>' . $name . '</>['.$child->id.','.$child->getYypResource()->key().']');
                $rootFolder->markEdited();
                $rootFolder->addChild($child);

                $originalResource = $child->getYypResource();
                $newResource = clone $originalResource;

                $newResource->prependFilePath('vendor/' . $newPackage->name() . '/');

                $this->recurse_copy(
                    $newPackage->getProjectPath() . '/' . $originalResource->resourcePathRoot(),
                    $this->getProjectPath() . '/' . $newResource->resourcePathRoot()
                );

                $this->projectEntity->addResource($newResource);
            }
        }

        $this->projectEntity()->save();
    }

    /**
     * @todo remove placeholder code
     * @param $src
     * @param $dst
     */
    private function recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst, 0777, true);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
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
        if (!$GLOBALS['dry']) {
            file_put_contents($this->projectPath . '/gmdepman.json', $this->getJson());
            file_put_contents($this->projectPath . '/gmdepman.gdm', $this->getGdm());
        }
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