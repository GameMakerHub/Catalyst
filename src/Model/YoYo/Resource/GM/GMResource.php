<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Traits\JsonUnpacker;

abstract class GMResource {

    use JsonUnpacker;

    /** @var \GMDepMan\Model\Uuid */
    public $id;

    /** @var string */
    public $modelName;

    /** @var string */
    public $filterType;

    /** @var array */
    public $_children = [];

    public function __construct($yyFilePath, DepManEntity $depManEntity)
    {
        $this->unpack(json_decode(file_get_contents($yyFilePath)), $depManEntity);
    }

    public function addChild(GMResource $child)
    {
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
}