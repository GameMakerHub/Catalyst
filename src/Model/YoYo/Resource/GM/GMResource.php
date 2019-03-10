<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Traits\JsonUnpacker;

abstract class GMResource
{

    use JsonUnpacker;

    /** @var \GMDepMan\Model\Uuid */
    public $id;

    /** @var string */
    public $modelName;

    /** @var string */
    public $name;

    /** @var string */
    public $filterType;

    /** @var array */
    private $_children = [];

    /** @var bool */
    private $_edited = false;

    /** @var string */
    private $_filePath;

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

    public function getJson()
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function save()
    {
        var_dump($this->getJson(), $this->getFilePath());
        //file_put_contents($this->getFilePath(), $this->getJson());
    }
}