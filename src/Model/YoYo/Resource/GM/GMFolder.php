<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Model\Uuid;

class GMFolder extends GMResource {
    /** @var Uuid[] */
    public $children;

    public function __construct($yyFilePath)
    {
        parent::__construct($yyFilePath);
        var_dump('Unpacked folder ' . $this->folderName);
    }
}