<?php
namespace GMDepMan\Entity;

use Assert\Assertion;
use GMDepMan\Exception\MalformedProjectFileException;
use GMDepMan\Model\Uuid;
use GMDepMan\Model\YoYo\Resource;
use GMDepMan\Traits\JsonUnpacker;
use Ramsey\Uuid\UuidInterface;

class YoYoProjectEntity {

    use JsonUnpacker;

    /** @var \stdClass */
    private $originalData;

    /** @var \GMDepMan\Model\Uuid */
    public $id;

    /** @var bool */
    public $IsDnDProject = false;

    /** @var \GMDepMan\Model\YoYo\Resource[] */
    public $resources;

    /** @var \GMDepMan\Model\YoYo\Resource\GM\GMResource[] */
    private $_children = [];

    /**
     * Load a JSON string in YYP format
     * @param string $json
     * @return $this
     */
    public function load(DepManEntity $depManEntity)
    {
        try {
            Assertion::file($depManEntity->getYypFilename());
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException('Project file ' . $depManEntity->getYypFilename() . ' does not exist');
        }

        $projectContents = file_get_contents($depManEntity->getYypFilename());
        $this->originalData = json_decode($projectContents);

        // Load all the resources into a map
        foreach ($this->originalData->resources as $resource) {
            $this->resources[$resource->Key] = new Resource($depManEntity, $resource);
        }

        // This is somewhat the same, but unused for now, so skipperino
        /*
        foreach ($this->originalData->parentProject->alteredResources as $resource) {
            $this->resources[$resource->Key] = new Resource($depManEntity, $resource);
        }
        */

        // Add children
        foreach ($this->resources as $item) {
            if (isset($item->gmResource()->filterType) && $item->gmResource()->filterType == Resource\GM\GMResourceTypes::GM_OPTIONS) {
                continue;
            }

            if (isset($item->gmResource()->children)) {
                foreach ($item->gmResource()->children as $childKey) {
                    if (!array_key_exists($childKey, $this->resources)) {
                        throw new MalformedProjectFileException('Resource with GUID ' . $childKey . ' was not found, but appears to be a child of some resource.');
                    }
                    $item->gmResource()->addChild($this->resources[$childKey]->gmResource());
                }
            }

            if (isset($item->gmResource()->filterType) && $item->gmResource()->filterType == Resource\GM\GMResourceTypes::GM_ROOT) {
                $this->_children = $item->gmResource()->getChildren();
            }
        }

        return $this;
    }

    /**
     * Return this project in YYP format
     */
    public function getJson()
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return Resource[]
     */
    public function getResources():array
    {
        return $this->resources;
    }

    public function getChildren():array
    {
        return $this->_children;
    }

}