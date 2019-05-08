<?php
namespace Catalyst\Model\YoYo;

use Assert\Assertion;
use Catalyst\Entity\CatalystEntity;
use Catalyst\Model\YoYo\Resource\GM\GMFolder;
use Catalyst\Model\YoYo\Resource\GM\GMResource;
use Catalyst\Model\YoYo\Resource\GM\GMResourceTypes;

class Resource implements \JsonSerializable {

    /** @var string */
    private $id;

    /** @var string */
    private $key;

    /** @var string */
    private $resourcePath;

    /** @var string */
    private $resourceType;

    /** @var GMResource */
    private $gmResource;

    /** @var array */
    private $children;

    public function __construct(\stdClass $gmJsonResource, $gmResource = null)
    {
        $this->id = $gmJsonResource->Value->id;
        $this->key = $gmJsonResource->Key;
        $this->resourcePath = $gmJsonResource->Value->resourcePath;
        $this->resourceType = $gmJsonResource->Value->resourceType;

        Assertion::keyExists(
            GMResourceTypes::TYPEMAP,
            $this->resourceType,
            'Type "' . $this->resourceType . '" not found in typemap'
        );

        $className = GMResourceTypes::TYPEMAP[$this->resourceType];
        if ($gmResource === null) {
            $this->gmResource = new $className($this->resourcePath);
        } else {
            $this->gmResource = $gmResource;
        }

        $this->gmResource->setYypResource($this);
    }

    public function key():string
    {
        return $this->key;
    }

    public function gmResource():GMResource
    {
        return $this->gmResource;
    }

    public function children():array
    {
        return $this->children;
    }

    public function jsonSerialize()
    {
        return self::makeJsonObject($this->key, $this->id, $this->resourceType, $this->resourcePath);
    }

    private static function makeJsonObject($key, $id, $type, $resourcePath)
    {
        $jsonObj = new \stdClass();
        $jsonObj->Key = $key;

        $jsonObj->Value = new \stdClass();
        $jsonObj->Value->id = $id;
        $jsonObj->Value->resourcePath = str_replace('/', '\\', $resourcePath);
        $jsonObj->Value->resourceType = $type;

        return $jsonObj;
    }

    public static function createFolder(GMFolder $resource):self {
        $jsonObj = self::makeJsonObject((string) $resource->id, (string) $resource->id, GMResourceTypes::GM_FOLDER, $resource->getFilePath());

        return new self(
            $jsonObj,
            $resource
        );
    }

    public function resourcePathRoot()
    {
        return dirname($this->resourcePath);
    }

}