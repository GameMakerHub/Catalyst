<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Model\Uuid;

class GMFolder extends GMResource {
    /** @var Uuid[] */
    public $children;
}