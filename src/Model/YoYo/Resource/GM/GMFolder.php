<?php
namespace Catalyst\Model\YoYo\Resource\GM;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Model\Uuid;
use Catalyst\Service\GMResourceService;
use Catalyst\Service\JsonService;
use Catalyst\Service\StorageService;

class GMFolder extends GMResource {
    /** @var Uuid[] */
    public $children = [];

    /** @var string */
    public $folderName;

    /** @var string|null */
    private $_fullName;

    public static function createNew(Uuid $uuid, $fullPath, $forType)
    {
        $filePath = 'views\\' . (string) $uuid . '.yy';

        $yyFile = new \stdClass();
        $yyFile->id = (string) $uuid;
        $yyFile->modelName = 'GMFolder';
        $yyFile->mvc = '1.1';
        $yyFile->name = (string) $uuid;
        $yyFile->children = [];
        $yyFile->filterType = $forType;
        $yyFile->folderName = basename($fullPath);
        $yyFile->isDefaultView = false;
        $yyFile->localisedFolderName = '';

        StorageService::getInstance()->writeYYFile($filePath, $yyFile);

        return self::createFromFile($filePath);
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