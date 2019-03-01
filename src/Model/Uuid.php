<?php
namespace GMDepMan\Model;

class Uuid implements \JsonSerializable {
    /** @var \Ramsey\Uuid\UuidInterface */
    public $value;

    public function __construct()
    {

    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function jsonSerialize()
    {
        return (string) $this;
    }

    public function serialize()
    {
        return (string) $this;
    }

    public function unpack($value)
    {
        $this->value = \Ramsey\Uuid\Uuid::fromString($value);
    }

    public function equals(\Ramsey\Uuid\Uuid $uuid):bool {
        return $this->value->equals($uuid);
    }
}