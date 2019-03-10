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

    /** @var DepManEntity */
    private $depManEntity;

    /**
     * Load a JSON string in YYP format
     * @param string $json
     * @return $this
     */
    public function load(DepManEntity $depManEntity)
    {
        $this->depManEntity = $depManEntity;

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

    public function gmFolderExists($foldername):bool
    {
        return $this->getGmFolderByName($foldername) instanceof Resource\GM\GMResource;
    }

    public function getGmFolderByName($foldername)
    {
        $folders = explode('/', $foldername);
        $children = $this->getChildren();
        while (count($folders)) {
            $looknow = array_shift($folders);
            $newChild = false;
            foreach ($children as $child) {
                //echo 'matching ' . (isset($child->folderName) ? $child->folderName : $child->name ) . ' ('.$child->isFolder().') vs ' . $looknow . PHP_EOL;
                if ($child->isFolder() && $child->folderName == $looknow) {
                    $newChild = $child;
                    break;
                }
            }
            if ($newChild == false) {
                return false;
            }
            $children = $newChild->getChildren();
        }
        return $newChild;
    }

    public function createGmFolder($foldername)
    {
        echo 'CREATE ' . $foldername . PHP_EOL;
        if ($this->gmFolderExists($foldername)) {
            //Already exists
            return true;
        }

        // Check parents
        $parentFolder = substr($foldername, 0, strrpos($foldername, '/', 0));
        if (!$this->gmFolderExists($parentFolder)) {
            $this->createGmFolder($parentFolder);
        }

        $folders = explode('/', $foldername);
        $children = $this->getChildren();
        while (count($folders) > 1) {
            $looknow = array_shift($folders);
            $newChild = false;
            foreach ($children as $child) {
                //echo 'matching ' . (isset($child->folderName) ? $child->folderName : $child->name ) . ' ('.$child->isFolder().') vs ' . $looknow . PHP_EOL;
                if ($child->isFolder() && $child->folderName == $looknow) {
                    $newChild = $child;
                    break;
                }
            }
            if ($newChild == false) {
                return false;
            }
            $children = $newChild->getChildren();
        }
        if (!$newChild instanceof Resource\GM\GMFolder) {
            throw new \InvalidArgumentException('Folder path is not a folder');
        }
        $newUuid = \Ramsey\Uuid\Uuid::uuid5(DepManEntity::UUID_NS, $foldername);
        $newObj = Resource\GM\GMFolder::createNew($newUuid, $folders[0], $newChild->filterType);
        $this->resources[] = Resource::createFolder($this->depManEntity, $newObj);
        $newChild->addChild($newObj);
        $newChild->markEdited();
        return true;
    }

    /**
     * Return this project in YYP format
     */
    public function getJson()
    {
        $newObject = $this->originalData;
        $newObject->resources = array_values($this->resources);
        return json_encode($newObject, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

    public function save()
    {
        foreach ($this->resources as $resource) {
            if ($resource->gmResource()->isEdited()) {
                $resource->gmResource()->save();
            }
        }
        if (!$GLOBALS['dry']) {
            file_put_contents($this->depManEntity->getYypFilename(), $this->getJson());
        }
        return true;
    }
}