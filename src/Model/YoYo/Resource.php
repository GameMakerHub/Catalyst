<?php
namespace GMDepMan\Model\YoYo;

use GMDepMan\Traits\JsonUnpacker;

class Resource {

    use JsonUnpacker;

    /** @var \GMDepMan\Model\YoYo\Resource\Key */
    public $Key;

    /** @var \GMDepMan\Model\YoYo\Resource\Value */
    public $Value;
}