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

        $this->YoYoProjectEntity = YoYoProjectEntity::createFromFile($this->path . DIRECTORY_SEPARATOR . $this->yyp);
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

    private $ignored = [];

    public function addIgnore($path)
    {
        $path = str_replace('\\', '/', $path);
        if (!in_array($path, $this->ignored)) {
            $this->ignored[] = $path;
        }
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
     * Store the new file information
     */
    public function save()
    {
        // Store the YYP file
        StorageService::getInstance()->saveEntity($this->YoYoProjectEntity());

        // Write the ignore file
        $this->saveIgnoreFile();
    }

    /**
     * @todo this is horrible and slow, but does the trick for now.
     */
    private function saveIgnoreFile()
    {
        $newContent = self::IGNORE_TOKEN . PHP_EOL . implode(PHP_EOL, $this->ignored) . PHP_EOL . self::IGNORE_TOKEN;

        $ignoreFile = $this->path() . '/.gitignore';
        $contents = '';
        if (StorageService::getInstance()->fileExists($ignoreFile)) {
            $contents = StorageService::getInstance()->getContents($ignoreFile);
        }

        preg_match('~(### CATALYST ###)[\s\S]+(### CATALYST ###)~', $contents, $matches);

        if (count($matches) == 0) {
            $contents .= PHP_EOL . $newContent;
        } else {
            $contents = str_replace($matches[0], $newContent, $contents);
        }

        StorageService::getInstance()->writeFile($ignoreFile, $contents);
    }


}