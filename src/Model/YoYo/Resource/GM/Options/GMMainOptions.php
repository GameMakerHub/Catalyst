<?php
namespace Catalyst\Model\YoYo\Resource\GM\Options;

use Catalyst\Model\YoYo\Resource\GM\GMResource;

class GMMainOptions extends GMResource {
    public static function createFromFile(string $_filePath): GMResource
    {
        //Gotta love consistency... This JSON file is not JSON, and the filepath is never correct.
        $stdClass = new \stdClass();
        $stdClass->name = '~MAIN OPTIONS FILE~';
        $stdClass->is_options_file = true;
        return self::createFromObject($_filePath, $stdClass);
    }
}