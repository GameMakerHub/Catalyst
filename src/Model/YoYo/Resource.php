<?php
namespace Catalyst\Model\YoYo;

use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource\GM\GMResource;

class Resource implements \JsonSerializable {

    /** @var Uuid */
    private $key;

    /** @var ResourceValue */
    private $value;

    /** @var string[] */
    private $configDeltas = [];

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

    public function gmResource(): GMResource
    {
        return $this->value->gmResource();
    }

    public function key(): Uuid
    {
        return $this->key;
    }

    public function value(): ResourceValue
    {
        return $this->value;
    }

    public function jsonSerialize(): \stdClass
    {
        $jsonObj = new \stdClass();
        $jsonObj->Key = (string) $this->key;
        $jsonObj->Value = $this->value->jsonSerialize();
        if (count($this->configDeltas)) {
            $jsonObj->configDeltas = $this->configDeltas;
        }

        return $jsonObj;
    }
}