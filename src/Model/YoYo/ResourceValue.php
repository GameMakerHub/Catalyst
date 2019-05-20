<?php
namespace Catalyst\Model\YoYo;

use Assert\Assertion;
use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource\GM\GMResourceTypes;

class ResourceValue implements \JsonSerializable {

    /** @var Uuid */
    private $id;

    /** @var string */
    private $resourcePath;

    /** @var string */
    private $resourceType;

    public static function createFromObject(\stdClass $gmJsonResource)
    {
        return new static(
            Uuid::createFromString($gmJsonResource->id),
            $gmJsonResource->resourcePath,
            $gmJsonResource->resourceType
        );
    }

    private function __construct(
        Uuid $id,
        string $resourcePath,
        string $resourceType
    ) {
        Assertion::keyExists(
            GMResourceTypes::TYPEMAP,
            $resourceType,
            'Resource type "' . $this->resourceType . '" not found in typemap'
        );

        $this->id = $id;
        $this->resourcePath = $resourcePath;
        $this->resourceType = $resourceType;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function resourcePath(): string
    {
        return $this->resourcePath;
    }

    public function resourceType(): string
    {
        return $this->resourceType;
    }

    public function jsonSerialize(): \stdClass
    {
        $jsonObj = new \stdClass();

        $jsonObj->id = (string) $this->id;
        $jsonObj->resourcePath = (string) str_replace('/', '\\', $this->resourcePath);
        $jsonObj->resourceType = (string) $this->resourceType;

        return $jsonObj;
    }
}