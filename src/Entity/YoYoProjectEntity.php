<?php
namespace GMDepMan\Entity;

use GMDepMan\Traits\JsonUnpacker;

class YoYoProjectEntity {

    use JsonUnpacker;

    /** @var \stdClass */
    private $originalData;

    /** @var \GMDepMan\Model\Uuid */
    public $id;

    /** @var bool */
    public $IsDnDProject = false;

    /** @var \GMDepMan\Model\YoYo\Resource[] */
    public $resources;

    /**
     * Load a JSON string in YYP format
     * @param string $json
     * @return $this
     */
    public function load(string $json)
    {
        $this->originalData = json_decode($json);

        $this->unpack($this->originalData);

        return $this;
    }

    /**
     * Return this project in YYP format
     */
    public function getJson()
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}