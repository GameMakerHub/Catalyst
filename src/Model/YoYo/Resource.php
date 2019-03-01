<?php
namespace GMDepMan\Model\YoYo;

use Assert\Assertion;
use GMDepMan\Entity\DepManEntity;
use GMDepMan\Model\YoYo\Resource\GM\GMResource;
use GMDepMan\Model\YoYo\Resource\GM\GMResourceTypes;

class Resource {

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

    /** @var DepManEntity */
    private $depManEntity;

    public function __construct(DepManEntity $depManEntity, \stdClass $gmJsonResource)
    {
        $this->depManEntity = $depManEntity;
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
        $this->gmResource = new $className($this->depManEntity->getProjectPath() . '/' . $this->resourcePath, $this->depManEntity);
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

}