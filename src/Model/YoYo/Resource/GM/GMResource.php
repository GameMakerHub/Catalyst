<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Traits\JsonUnpacker;

class GMResource {

    use JsonUnpacker;

    /** @var \GMDepMan\Model\Uuid */
    public $id;

    public function __construct($yyFilePath)
    {
        $this->unpack(json_decode(file_get_contents($yyFilePath)));
    }

}