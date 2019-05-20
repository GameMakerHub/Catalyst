<?php
namespace Catalyst\Model\YoYo;

use Catalyst\Model\Uuid;

class Resource implements \JsonSerializable {

    /** @var Uuid */
    private $key;

    /** @var ResourceValue */
    private $value;

    public static function createFromObject(\stdClass $gmJsonResource): Resource
    {
        return new static(
            Uuid::createFromString($gmJsonResource->Key),
            ResourceValue::createFromObject($gmJsonResource->Value)
        );
    }

    private function __construct(
        Uuid $key,
        ResourceValue $value
    ) {
        $this->key = $key;
        $this->value = $value;
    }

    public function key():Uuid
    {
        return $this->key;
    }

    public function jsonSerialize(): \stdClass
    {
        $jsonObj = new \stdClass();
        $jsonObj->Key = (string) $this->key;
        $jsonObj->Value = $this->value->jsonSerialize();

        return $jsonObj;
    }
}