<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Model\Uuid;

class GMFolder extends GMResource {
    /** @var Uuid[] */
    public $children = [];

    /** @var string */
    public $folderName;

    public $mvc = '1.1';

    public $isDefaultView = false;

    public $localisedFolderName = '';

    public static function createNew($folderName, $forType)
    {
        $newObj = new self(false);

        $newUuid = \Ramsey\Uuid\Uuid::uuid5(DepManEntity::UUID_NS, $folderName);
        $newObj->id = new Uuid();
        $newObj->id->value = $newUuid;
        $newObj->name = (string) $newObj->id;
        $newObj->filterType = $forType;
        $newObj->modelName = GMResourceTypes::GM_FOLDER;
        $newObj->folderName = $folderName;

        return $newObj;
    }
}