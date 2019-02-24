<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

class GMRoom extends GMResource {
    public function __construct($yyFilePath)
    {
        parent::__construct($yyFilePath);
        var_dump('Unpacked room ' . $this->name);
    }
}