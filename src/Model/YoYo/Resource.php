<?php
namespace Catalyst\Model\YoYo;

use Catalyst\Interfaces\SaveableEntityInterface;
use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource\GM\GMResource;

class Resource implements \JsonSerializable {

    /** @var Uuid */
    private $key;

    /** @var ResourceValue */
    private $value;

    /** @var string[] */
    private $configDeltas = [];

    /**
     * @param GMResource $GMResource
     * @return Resource
     */
    public static function createFromObject(\stdClass $gmJsonResource)
    {
        return new static(
            Uuid::createFromString($gmJsonResource->Key),
            ResourceValue::createFromObject($gmJsonResource->Value)
        );
    }

    /**
     * @param GMResource $GMResource
     * @return Resource
     */
    public static function createFromGMResource(GMResource $GMResource)
    {
        $resourceValue = ResourceValue::createFromGMResource($GMResource);
        return new static(
            Uuid::createFromString($GMResource->id),
            $resourceValue
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