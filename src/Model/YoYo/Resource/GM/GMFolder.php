<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Model\Uuid;

class GMFolder extends GMResource {
    /** @var Uuid[] */
    public $children = [];

    /** @var string */
    public $folderName;

    /** @var string|null */
    private $_fullName;

    public static function createNew(\Ramsey\Uuid\UuidInterface $uuid, $folderName, $forType, $fullName)
    {
        $newObj = new self('views\\' . $uuid . '.yy', null, false);
        $newObj->id = new Uuid();
        $newObj->id->value = $uuid;
        $newObj->name = (string) $newObj->id;
        $newObj->filterType = $forType;
        $newObj->modelName = GMResourceTypes::GM_FOLDER;
        $newObj->folderName = $folderName;
        $newObj->setFullName($fullName);
        $newObj->markEdited();

        return $newObj;
    }

    public function setFullName($fullName)
    {
        $this->_fullName = $fullName;
    }

    public function getFullName()
    {
        return $this->_fullName;
    }
}