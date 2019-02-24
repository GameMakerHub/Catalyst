<?php
namespace GMDepMan\Model\YoYo\Resource;

use GMDepMan\Traits\JsonUnpacker;

class Value {

    use JsonUnpacker;

    /** @var \GMDepMan\Model\Uuid */
    public $id;

    /** @var string */
    public $resourcePath;

    /** @var string */
    public $resourceType;
}