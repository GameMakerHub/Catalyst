<?php
namespace Catalyst\Entity;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Interfaces\SaveableEntityInterface;
use Catalyst\Service\StorageService;

class CatalystEntity implements SaveableEntityInterface {

    const IGNORE_TOKEN = '### CATALYST ###';

    const UUID_NS = '00000000-1337-fafa-0000-dededededede';

    const VENDOR_FOLDER_NAME = 'vendor';

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

    /** @var YoYoProjectEntity */
    private $YoYoProjectEntity;

    /** @var array */
    private $gitIgnore = [];

    /** @var array */
    private $ignoredResources = [];

    private function __construct(
        string $path,
        string $name,
        string $description,
        string $license,
        string $homepage,
        string $yyp,
        array $require,
        array $repositories,
        array $gitIgnore,
        array $ignoredResources,
        $ignoreMissing = false
    ) {
        $this->path = realpath($path);
        $this->name = $name;
        $this->description = $description;
        $this->license = $license;
        $this->homepage = $homepage;
        $this->yyp = $yyp;

        $this->require = $require;
        $this->repositories = $repositories;

        $this->YoYoProjectEntity = YoYoProjectEntity::createFromFile($this->path . DIRECTORY_SEPARATOR . $this->yyp, $ignoreMissing);

        $this->gitIgnore = $gitIgnore;
        $this->ignoredResources = $ignoredResources;
    }

    public static function createNew(
        string $path,
        string $name,
        string $description,
        string $license,
        string $homepage,
        string $yyp
    ) {
        return new self($path, $name, $description, $license, $homepage, $yyp, [], [], [], [], false);
    }

    public static function createFromPath($path, $ignoreMissing = false)
    {
        try {
            $config = StorageService::getInstance()->getJson($path . '/catalyst.json');
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException('Catalyst file is not found in "' . $path . '".');
        } catch (MalformedJsonException $e) {
            throw new \RuntimeException('catalyst.json is malformed');
        }

        $gitIgnored = [];
        try {
            $ignoreData = StorageService::getInstance()->getContents($path . '/.gitignore');
            $ignoreData = str_replace("\r\n", "\n", $ignoreData);
            $openTag = strpos($ignoreData, self::IGNORE_TOKEN);
            if ($openTag != false) {
                $closeTag = strpos($ignoreData, self::IGNORE_TOKEN, $openTag+1);
                if ($closeTag != false) {
                    $tagLength = strlen(self::IGNORE_TOKEN);
                    $gitIgnored = explode(
                        "\n",
                        substr(
                            $ignoreData,
                            $openTag+$tagLength+1,
                            $closeTag-$openTag-$tagLength-2
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            //no ignore data - ok no problemo
        }

        return new self(
            $path,
            $config->name ?? null,
            $config->description ?? null,
            $config->license ?? null,
            $config->homepage ?? null,
            $config->yyp ?? null,
            (array) ($config->require ?? []),
            (array) ($config->repositories ?? []),
            $gitIgnored,
            $config->ignore ?? [],
            $ignoreMissing
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
        if (count($this->ignoredResources)) { $jsonObj->ignore = $this->ignoredResources; }

        return json_encode($jsonObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function gitIgnore()
    {
        return $this->gitIgnore;
    }

    public function removeGitIgnore($value): bool
    {
        $value = str_replace('\\', '/', $value);
        if (($key = array_search($value, $this->gitIgnore)) !== false) {
            unset($this->gitIgnore[$key]);
            return true;
        }
        return false;
    }

    public function addGitIgnore($path)
    {
        $path = str_replace('\\', '/', $path);
        if (!in_array($path, $this->gitIgnore)) {
            $this->gitIgnore[] = $path;
        }
    }

    public function hasGitIgnore($value):bool
    {
        return (($key = array_search($value, $this->gitIgnore)) !== false);
    }

    /**
     * Store the new file information
     */
    public function save()
    {
        // Store the YYP file
        StorageService::getInstance()->saveEntity($this->YoYoProjectEntity());

        // Write the ignore file
        $this->saveGitIgnoreFile();
    }

    /**
     * @todo this is horrible and slow, but does the trick for now.
     */
    private function saveGitIgnoreFile()
    {
        $newContent = self::IGNORE_TOKEN . PHP_EOL . implode(PHP_EOL, $this->gitIgnore) . PHP_EOL . self::IGNORE_TOKEN;

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