<?php
namespace Catalyst\Entity;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Catalyst\Exception\FileNotFoundException;
use Catalyst\Exception\MalformedProjectFileException;
use Catalyst\Interfaces\SaveableEntityInterface;
use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource;
use Catalyst\Model\YoYo\ResourceValue;
use Catalyst\Service\GMResourceService;
use Catalyst\Service\JsonService;
use Catalyst\Service\StorageService;
use Catalyst\Traits\JsonUnpacker;
use Ramsey\Uuid\UuidInterface;

class YoYoProjectEntity implements SaveableEntityInterface {

    /** @var string */
    private $filePath;

    /** @var \stdClass */
    private $originalData;

    /** @var \Catalyst\Model\Uuid */
    public $id;

    /** @var string */
    public $modelName;

    /** @var string */
    public $mvc;

    /** @var bool */
    public $IsDnDProject;

    /** @var string[] */
    public $configs;

    /** @var bool */
    public $option_ecma;

    /** @var \stdClass */ //@todo maybe another YoYoProjectEntity ?
    public $parentProject;

    /** @var \Catalyst\Model\YoYo\Resource[] */
    public $resources;

    /** @var string[] */
    public $script_order;

    /** @var string */
    public $tutorial;

    private function __construct(
        string $filePath,
        \stdClass $originalData,
        Uuid $id,
        string $modelName,
        string $mvc,
        bool $IsDnDProject,
        array $configs,
        bool $option_ecma,
        \stdClass $parentProject,
        array $resources,
        array $script_order,
        string $tutorial
    ) {
        Assertion::allIsInstanceOf($resources, Resource::class);

        $this->filePath = $filePath;
        $this->originalData = $originalData;
        $this->id = $id;
        $this->modelName = $modelName;
        $this->mvc = $mvc;
        $this->IsDnDProject = $IsDnDProject;
        $this->configs = $configs;
        $this->option_ecma = $option_ecma;
        $this->parentProject = $parentProject;
        $this->script_order = $script_order;
        $this->tutorial = $tutorial;
        $this->resources = $resources;

        // Now link all resources / children to each other
        foreach ($this->resources as $resource) {
            $gmResource = $resource->gmResource();
            if ($gmResource->isFolder()) {
                foreach ($gmResource->children as $childId) {
                    if (!isset($this->resources[$childId])) {
                        throw new \Exception('Could not find child ID ' . $childId . ' in resource list;');
                    }
                    $gmResource->linkChildResource($this->resources[$childId]->gmResource());
                }
            }
        }
    }

    public static function createFromFile($filePath)
    {
        try {
            Assertion::file($filePath);

            // Load file
            $originalData = StorageService::getInstance()->getJson($filePath);
            $backupdir = getcwd();
            chdir(dirname($filePath));

            // Load resources
            $resources = [];
            foreach ($originalData->resources as $resource) {
                $resources[$resource->Key] = Resource::createFromObject($resource);
            }

            // Also create a reference for the parent project UUID's
            if (isset($originalData->parentProject)) {
                if (isset($originalData->parentProject->alteredResources)) {
                    foreach ($originalData->parentProject->alteredResources as $resource) {
                        $resources[$resource->Key] = Resource::createFromObject($resource);
                    }
                }
            }

            chdir($backupdir);

            return new self(
                $filePath,
                $originalData,
                Uuid::createFromString($originalData->id),
                $originalData->modelName,
                $originalData->mvc,
                $originalData->IsDnDProject,
                $originalData->configs,
                $originalData->option_ecma,
                $originalData->parentProject,
                $resources,
                $originalData->script_order,
                $originalData->tutorial
            );
        } catch (\Exception $e) {
            throw new MalformedProjectFileException(
                'YYP file could not be loaded: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function resourceNameExists($resourceName)
    {
        foreach ($this->resources as $item) {
            if ($item->value()->gmResource()->name == $resourceName) {
                return true;
            }
        }
        return false;
    }

    public function uuidExists(Uuid $uuid): bool
    {
        foreach ($this->resources as $item) {
            if ($item->key()->equals($uuid)) {
                return true;
            }
            if ($item->value()->id()->equals($uuid)) {
                return true;
            }
        }
        return false;
    }

    public function getFreeUuid(): Uuid
    {
        $newUuid = Uuid::createRandom();
        while ($this->uuidExists($newUuid)) {
            $newUuid = Uuid::createRandom();
            //If this creates an infinite loop you have a real shitload of entities
        }
        return Uuid::createFromString($newUuid);
    }

    public function createFolder(string $folderName, $type)
    {
        // Early return if it exists
        $folder = $this->getByInternalPath($folderName);
        if ($folder !== false) {
            //echo '--Full path exists: ' . $folderName . PHP_EOL;
            return $folder;
        }

        // It doesn't exist, try to make the folder structure
        $folderDirectory = explode('/', $folderName);
        $realPath = '';

        foreach ($folderDirectory as $directory) {
            $realPath .= '/' . $directory;
            //echo ' Checking for ' . $realPath . PHP_EOL;
            $folder = $this->getByInternalPath($realPath);
            if ($folder === false) {
                //echo '   that does not exist, make it and add it to our parent: ' . $parent->folderName . PHP_EOL;

                $folder = Resource\GM\GMFolder::createNew($this->getFreeUuid(), $realPath, $type);

                $parent->addNewChildResource($folder);
                $this->addResource($folder);
                echo 'Adding ' . $folder->folderName . ' to ' . $parent->folderName . PHP_EOL;
            }
            $parent = $folder;
        }

        return $folder;
    }

    public function addResource(Resource\GM\GMResource $gmResource)
    {
        $resource = Resource::createFromGMResource($gmResource);
        $this->resources[(string) $resource->key()] = $resource;
        StorageService::getInstance()->writeFile($resource->gmResource()->getFilePath(), $resource->gmResource()->getJson());
    }

    /**
     * @param $realPath
     * @return bool|Resource\GM\GMResource
     */
    public function getByInternalPath($realPath)
    {
        $count = 0;
        if (strpos($realPath, '/') === 0) {
            $realPath = substr($realPath, 1, strlen($realPath)-1);
        }

        $folderDirectory = explode('/', $realPath);
        foreach ($folderDirectory as $directory) {
            if ($count == 0) {
                // first
                $resource = $this->getRoot()->gmResource()->findChildResourceByName($directory);
            } else {
                $resource = $resource->findChildResourceByName($directory);
            }

            if ($resource === false) {
                return false;
            }

            $count++;
        }

        return $resource;
    }

    public function createFolderIfNotExists(string $folderName, $type)
    {
        return $this->createFolder($folderName, $type);
    }

    public function getFileContents(): string
    {
        return $this->getJson();
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getRoot(): ResourceValue
    {
        foreach ($this->resources as $resource) {
            if ($resource->gmResource()->isFolder()) {
                if ($resource->gmResource()->filterType == GMResourceService::GM_ROOT) {
                    return $resource->value();
                }
            }
        }
        throw new \RuntimeException('No root resource was found!');
    }

    /**
     * Return this project in YYP format
     */
    public function getJson()
    {
        $newObject = $this->originalData;

        $resourcesClone = $this->resources;
        array_pop($resourcesClone);
        $newObject->resources = array_values($resourcesClone);

        // Remove Main Options that might have been added
        $newObject->resources = array_filter($newObject->resources, function($value) {
            if ($value->gmResource()->getTypeName() == 'MainOptions') {
                return false;
            }
            return true;
        });

        $newObject->resources = array_values($newObject->resources);

        $newObject->script_order = $this->script_order;
        return JsonService::encode($newObject);
    }

}