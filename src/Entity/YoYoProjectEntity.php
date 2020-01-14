<?php
namespace Catalyst\Entity;

use Assert\Assertion;
use Catalyst\Exception\MalformedProjectFileException;
use Catalyst\Interfaces\SaveableEntityInterface;
use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource;
use Catalyst\Model\YoYo\ResourceValue;
use Catalyst\Service\GMResourceService;
use Catalyst\Service\JsonService;
use Catalyst\Service\StorageService;

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
        string $tutorial,
        $ignoreMissing = false
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
                    try {
                        if (!isset($this->resources[$childId])) {
                            throw new \Exception('Could not find child ID ' . $childId . ' in resource list;');
                        }
                        $gmResource->linkChildResource($this->resources[$childId]->gmResource());
                    } catch (\Exception $e) {
                        if (!$ignoreMissing) throw $e;
                    }

                }
            }
        }
    }

    public static function createFromFile($filePath, $ignoreMissing = false)
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
                try {
                    $resources[$resource->Key] = Resource::createFromObject($resource);
                } catch (\Exception $e) {
                    if (!$ignoreMissing) throw $e;
                }
            }

            // Also create a reference for the parent project UUID's
            if (isset($originalData->parentProject)) {
                if (isset($originalData->parentProject->alteredResources)) {
                    foreach ($originalData->parentProject->alteredResources as $resource) {
                        try {
                            $resources[$resource->Key] = Resource::createFromObject($resource);
                        } catch (\Exception $e) {
                            if (!$ignoreMissing) throw $e;
                        }
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
                $originalData->tutorial,
                $ignoreMissing
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

    public function createFolder(CatalystEntity $project, string $folderName, $type)
    {
        $folderName = str_replace('\\', '/', $folderName);
        // Early return if it exists
        $folder = $this->getByInternalPath($folderName);
        if ($folder !== false) {
            return $folder;
        }

        // It doesn't exist, try to make the folder structure
        $folderDirectory = explode('/', $folderName);
        $realPath = '';

        foreach ($folderDirectory as $directory) {
            $realPath .= '/' . $directory;
            $folder = $this->getByInternalPath($realPath);
            if ($folder === false) {
                //echo '   that does not exist, make it and add it to our parent: ' . $parent->folderName . PHP_EOL;
                $folder = Resource\GM\GMFolder::createNew(Uuid::createFromString(md5($realPath)), $realPath, $type);

                $parent->addNewChildResource($folder);
                $this->addResource($folder);

                // Add the generated view file to the gitignore list
                $project->addGitIgnore($folder->getFilePath());
            }
            $parent = $folder;
        }

        return $folder;
    }

    public function addResource(Resource\GM\GMResource $gmResource)
    {
        $resource = Resource::createFromGMResource($gmResource);
        $this->resources[(string) $resource->key()] = $resource;
        StorageService::getInstance()->saveEntity($resource->gmResource());
    }

    /**
     * Removes a UUID from both the resource list, and from any view files that have this linked as a child
     * @param Uuid $uuid
     */
    public function removeUuidReference(Uuid $uuid)
    {
        foreach ($this->resources as $resource) {
            if ($resource->gmResource()->isFolder()) {
                $resource->gmResource()->removeChildResourceByUuid($uuid);
            }
        }
        unset($this->resources[(string) $uuid]);

        // Remove from script order - GM auto generates this
        if (($key = array_search((string) $uuid, $this->script_order)) !== false) {
            unset($this->script_order[$key]);
        }
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

    public function createFolderIfNotExists(CatalystEntity $project, string $folderName, $type)
    {
        return $this->createFolder($project, $folderName, $type);
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

        // Remove Main Options that might have been added
        $resourcesClone = array_filter($resourcesClone, function($value) {
            if ($value->gmResource()->getTypeName() == 'MainOptions') {
                return false;
            }
            return true;
        });

        // Cast to an array of objects
        $newObject->resources = array_values($resourcesClone);
        //@todo make sure this sorts the way GM does, prevent loads of changes @see
        $newObject->script_order = $this->script_order;
        return JsonService::encode($newObject);
    }

}