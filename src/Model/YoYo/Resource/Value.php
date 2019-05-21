<?php
namespace Catalyst\Model\YoYo\Resource;

use Assert\Assertion;
use Catalyst\Entity\CatalystEntity;
use Catalyst\Model\YoYo\Resource\GM\GMResource;
use Catalyst\Service\GMResourceService;
use Catalyst\Traits\JsonUnpacker;

class Value {
    use JsonUnpacker {
        unpack as protected traitUnpack;
    }

    /** @var \Catalyst\Model\Uuid */
    public $id;

    /** @var string */
    public $resourcePath;

    /** @var string */
    public $resourceType;

    /** @var \Catalyst\Model\YoYo\Resource\GM\GMResource */
    private $resource;

    public function unpack($originalData, CatalystEntity $depmanEntity)
    {
        $this->traitUnpack($originalData, $depmanEntity);

        if ($this->resourceType == 'GMResource') {
            return; //Ignore this layer
        }

        Assertion::keyExists(
            GMResourceService::TYPEMAP,
            $this->resourceType,
            'Type "' . $this->resourceType . '" not found in typemap'
        );

        $className = GMResourceService::TYPEMAP[$this->resourceType];
        $this->resource = new $className($depmanEntity->getProjectPath() . '/' . $this->resourcePath, $depmanEntity);
    }

    public function getGmResource():GMResource
    {
        return $this->resource;
    }
}