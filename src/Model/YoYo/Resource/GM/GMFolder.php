<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Model\Uuid;

class GMFolder extends GMResource {
    /** @var Uuid[] */
    public $children = [];

    /** @var string */
    public $folderName;

    public static function createNew(\Ramsey\Uuid\UuidInterface $uuid, $folderName, $forType)
    {
        $newObj = new self('views\\' . $uuid . '.yy', null, false);
        $newObj->id = new Uuid();
        $newObj->id->value = $uuid;
        $newObj->name = (string) $newObj->id;
        $newObj->filterType = $forType;
        $newObj->modelName = GMResourceTypes::GM_FOLDER;
        $newObj->folderName = $folderName;
        $newObj->markEdited();

        return $newObj;
    }
}