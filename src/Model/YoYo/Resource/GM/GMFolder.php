<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Model\Uuid;

class GMFolder extends GMResource {
    /** @var Uuid[] */
    public $children = [];

    /** @var string */
    public $folderName;

    public static function createNew($folderName, $forType)
    {
        $newUuid = \Ramsey\Uuid\Uuid::uuid5(DepManEntity::UUID_NS, $folderName);

        $newObj = new self('views\\' . $newUuid . '.yy', null, false);
        $newObj->id = new Uuid();
        $newObj->id->value = $newUuid;
        $newObj->name = (string) $newObj->id;
        $newObj->filterType = $forType;
        $newObj->modelName = GMResourceTypes::GM_FOLDER;
        $newObj->folderName = $folderName;
        $newObj->markEdited();

        return $newObj;
    }
}