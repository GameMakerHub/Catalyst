<?php
namespace Catalyst\Entity;

use Assert\Assertion;
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
                    return $resource;
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
        $newObject->resources = array_values($this->resources);
        $newObject->script_order = $this->script_order;

        return JsonService::encode($newObject);
    }

}