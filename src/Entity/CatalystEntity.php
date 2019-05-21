<?php
namespace Catalyst\Entity;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Interfaces\SaveableEntityInterface;
use Catalyst\Model\YoYo\Resource\GM\GMFolder;
use Catalyst\Service\StorageService;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class CatalystEntity implements SaveableEntityInterface {

    const IGNORE_TOKEN = '### CATALYST ###';

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

    /** @var OLDYoYoProjectEntity */
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

    /** @var string */
    private $path;

    /** @var array */
    private $require = [];

    /** @var array */
    private $repositories = [];

    /** @var OLDYoYoProjectEntity */
    private $YoYoProjectEntity;

    private function __construct(
        string $path,
        string $name,
        string $description,
        string $license,
        string $homepage,
        string $yyp,
        array $require,
        array $repositories
    ) {
        $this->path = realpath($path);
        $this->name = $name;
        $this->description = $description;
        $this->license = $license;
        $this->homepage = $homepage;
        $this->yyp = $yyp;

        $this->require = $require;
        $this->repositories = $repositories;

        $this->YoYoProjectEntity = YoYoProjectEntity::createFromFile($this->yyp);
    }

    public static function createNew(
        string $path,
        string $name,
        string $description,
        string $license,
        string $homepage,
        string $yyp
    ) {
        return new self($path, $name, $description, $license, $homepage, $yyp, [], []);
    }

    public static function createFromPath($path)
    {
        try {
            $config = StorageService::getInstance()->getJson($path . '/catalyst.json');
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException('Catalyst file is not found in "' . $path . '".');
        } catch (MalformedJsonException $e) {
            throw new \RuntimeException('catalyst.json is malformed');
        }

        return new self(
            $path,
            $config->name ?? null,
            $config->description ?? null,
            $config->license ?? null,
            $config->homepage ?? null,
            $config->yyp ?? null,
            (array) ($config->require ?? []),
            (array) ($config->repositories ?? [])
        );
    }

    public function hasPackage(string $packageName): bool {
        return array_key_exists($packageName, $this->require);
    }

    public function addRequire(string $package, string $version) {
        $this->require[$package] = $version;
    }

    /* GETTER METHODS */

    public function YoYoProjectEntity(): YoYoProjectEntity
    {
        return $this->YoYoProjectEntity;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function license(): string
    {
        return $this->license;
    }

    public function homepage(): string
    {
        return $this->homepage;
    }

    public function yyp(): string
    {
        return $this->yyp;
    }

    public function require(): array
    {
        return $this->require;
    }

    public function repositories(): array
    {
        return $this->repositories;
    }

    /* @see SaveableEntityInterface METHODS */

    public function getFilePath(): string
    {
        return $this->path . '/catalyst.json';
    }

    public function getFileContents() : string
    {
        $jsonObj = new \stdClass();

        $jsonObj->name = $this->name;
        $jsonObj->description = $this->description;
        $jsonObj->license = $this->license;
        $jsonObj->homepage = $this->homepage;
        $jsonObj->yyp = $this->yyp;
        if (count($this->require)) { $jsonObj->require = $this->require; }
        if (count($this->repositories)) { $jsonObj->repositories = $this->repositories; }

        return json_encode($jsonObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @deprecated
     */
    public function installPackage(CatalystEntity $newPackage, OutputInterface $output)
    {
        // Loop through all files and copy / add them to this project
        $this->loopIn($output, $newPackage, $newPackage->projectEntity()->getChildren(),0);

        // Copy the datafiles needed
        foreach (glob($newPackage->getProjectPath() . '/datafiles/*') as $datafile) {
            @mkdir($this->getProjectPath() . '/datafiles');
            $destFile =  '/datafiles/' . basename($datafile);
            copy($datafile, $this->getProjectPath() . $destFile);
            $this->addIgnore($destFile);
        }

        $this->projectEntity()->save();
        $this->save();
    }

    /**
     * @param OutputInterface $output
     * @param CatalystEntity $newPackage
     * @param \Catalyst\Model\YoYo\Resource\GM\GMResource[] $children
     * @param int $level
     * @param GMFolder|null $rootFolder
     * @throws \Exception
     */
    private function loopIn(OutputInterface $output, CatalystEntity $newPackage, array $children, $level = 0, GMFolder $rootFolder = null) {
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
                    $nextFolder = $this->projectEntity()->createGmFolder($name . '/vendor/' . $newPackage->name());
                } else {
                    $nextFolder = $this->projectEntity()->createGmFolder($rootFolder->getFullName() . '/' . $name);
                    $this->addIgnore($nextFolder->getFilePath());
                }
                $output->writeln('    '. str_repeat('|  ', $level).'\__ <fg=cyan>' . $name . '</>['.$child->id.','.$child->getYypResource()->key().']', Output::VERBOSITY_VERY_VERBOSE);
                $this->loopIn($output, $newPackage, $child->getChildren(), $level+1, $nextFolder);
            }

            if (!$isFolder) {
                $output->writeln('    ' . str_repeat('|  ', $level).'\__ <fg=green>' . $name . '</>['.$child->id.','.$child->getYypResource()->key().']', Output::VERBOSITY_VERY_VERBOSE);
                $rootFolder->markEdited();
                $rootFolder->addChild($child);

                $resource = $child->getYypResource();

                $this->recurse_copy(
                    $newPackage->getProjectPath() . '/' . $resource->resourcePathRoot(),
                    $this->getProjectPath() . '/' . $resource->resourcePathRoot()
                );

                $this->projectEntity->addResource($resource);
                $this->addIgnore($resource->resourcePathRoot());
            }
        }
    }

    private $ignored = [];

    /**
     * @deprecated
     */
    public function addIgnore($path)
    {
        $path = str_replace('\\', '/', $path);
        if (!in_array($path, $this->ignored)) {
            $this->ignored[] = $path;
        }
    }

    /**
     * @deprecated
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
                } else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @deprecated
     * @return string
     */
    public function getProjectPath() : string
    {
        return realpath($this->projectPath);
    }

    /**
     * @deprecated
     * @return string
     */
    public function getYypFilename() : string
    {
        return realpath($this->projectPath) . '/' . $this->yyp;
    }

    /**
     * @deprecated
     * Persist the files and write to disk
     */
    public function save()
    {
        //file_put_contents($this->projectPath . '/catalyst.json', $this->getJson());
        //$this->saveIgnoreFile();
    }

    /**
     * @deprecated
     * @todo this is horrible and slow, but does the trick for now.
     */
    private function saveIgnoreFile()
    {
        $newContent = self::IGNORE_TOKEN . PHP_EOL . implode(PHP_EOL, $this->ignored) . PHP_EOL . self::IGNORE_TOKEN;

        $ignoreFile = $this->projectPath . '/.gitignore';
        $contents = '';
        if (file_exists($ignoreFile)) {
            $contents = file_get_contents($ignoreFile);
        }

        preg_match('~(### CATALYST ###)[\s\S]+(### CATALYST ###)~', $contents, $matches);

        if (count($matches) == 0) {
            $contents .= PHP_EOL . $newContent;
        } else {
            $contents = str_replace($matches[0], $newContent, $contents);
        }

        file_put_contents($ignoreFile, $contents);
    }


}