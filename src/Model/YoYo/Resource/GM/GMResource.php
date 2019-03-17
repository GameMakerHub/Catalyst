<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Model\YoYo\Resource;
use GMDepMan\Traits\JsonUnpacker;

abstract class GMResource implements \JsonSerializable
{

    use JsonUnpacker;

    /** @var \GMDepMan\Model\Uuid */
    public $id;

    /** @var string */
    public $modelName;

    /** @var string */
    public $mvc = '1.1';

    /** @var string */
    public $name;

    /** @var bool */
    public $isDefaultView = false;

    /** @var string */
    public $localisedFolderName = '';

    /** @var string */
    public $filterType;

    /** @var array */
    private $_children = [];

    /** @var bool */
    private $_edited = false;

    /** @var string */
    private $_filePath;

    /** @var Resource */
    private $_yypResource;

    /**
     * GMResource constructor.
     * @param $yyFilePath|false
     * @param DepManEntity $depManEntity
     */
    public function __construct(string $yyFilePath, DepManEntity $depManEntity = null, $load = true)
    {
        $this->_filePath = $yyFilePath;
        if ($load) {
            $this->unpack(json_decode(file_get_contents($depManEntity->getProjectPath() . '/' . str_replace('\\', '/', $yyFilePath))), $depManEntity);
        }
    }

    public function addChild(GMResource $child)
    {
        if (!isset($this->children)) {
            throw new \Exception('Cannot add child to resource that has no children property');
        }
        if (array_search((string) $child->id, $this->children) === false) {
            $this->children[] = (string) $child->id;
        }

        $this->_children[] = $child;
    }

    public function removeChild(string $id)
    {
        if (!isset($this->children)) {
            return;
        }

        foreach ($this->_children as $key => $child) {
            if ($child->id == $id) {
                echo 'Deleting ' . $id . ' from ' . $this->id . '('.$key.')' . PHP_EOL;

                foreach ($this->children as $key2 => $child2) {
                    if ($child2 == $id) {
                        unset($this->children[$key2]);
                    }
                }

                unset($this->_children[$key]);
                $this->markEdited();
            }
        }

    }

    public function addChildren(array $children)
    {
        $this->_children = $children;
    }

    public function getChildren()
    {
        return $this->_children;
    }

    public function isFolder():bool
    {
        return $this->modelName == GMResourceTypes::GM_FOLDER;
    }

    public function markEdited():void
    {
        $this->_edited = true;
    }

    public function isEdited():bool
    {
        return $this->_edited;
    }

    public function getFilePath()
    {
        return $this->_filePath;
    }

    public function jsonSerialize()
    {
        //Creating a new object to get the order of the JSON file OK, so the diff doesn't explode
        $newObj = new \stdClass();
        $newObj->id = $this->id;
        $newObj->modelName = $this->modelName;
        $newObj->mvc = $this->mvc;
        $newObj->name = $this->name;
        if (isset($this->children)) {
            $newObj->children = $this->children;
        }
        $newObj->filterType = $this->filterType;
        if (isset($this->folderName)) {
            $newObj->folderName = $this->folderName;
        }
        $newObj->isDefaultView = $this->isDefaultView;
        if (isset($this->localisedFolderName)) {
            $newObj->localisedFolderName = $this->localisedFolderName;
        }

        return $newObj;
    }

    public function getJson()
    {
        return str_replace("\n", "\r\n", json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function save()
    {
        //var_dump($this->getJson(), $this->getFilePath());
        if (!$GLOBALS['dry']) {
            file_put_contents($this->getFilePath(), $this->getJson());
        }
    }

    public function getYypResource()
    {
        return $this->_yypResource;
    }

    public function setYypResource(Resource $yypResource)
    {
        $this->_yypResource = $yypResource;
    }

    public function delete()
    {
        //echo 'TO RM:  ' . $this->getFilePath() . '('.dirname($this->getFilePath()).')';
        if ($this instanceof GMFolder) {
            unlink($this->getFilePath());
        } else {
            $this->rrmdir(dirname($this->getFilePath()));
        }

    }

    private function rrmdir($path) {
        // Open the source directory to read in files
        $i = new \DirectoryIterator($path);
        foreach($i as $f) {
            if($f->isFile()) {
                unlink($f->getRealPath());
            } else if(!$f->isDot() && $f->isDir()) {
                $this->rrmdir($f->getRealPath());
            }
        }
        rmdir($path);
    }
}