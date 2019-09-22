<?php
namespace Catalyst\Model;

class Uuid implements \JsonSerializable {

    /** @var \Ramsey\Uuid\Uuid */
    public $value;

    public static function createFromString(string $value)
    {
        return new self($value);
    }

    public static function createRandom()
    {
        return new self((string) \Ramsey\Uuid\Uuid::uuid4());
    }

    private function __construct($value)
    {
        $this->value = \Ramsey\Uuid\Uuid::fromString($value);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function internalValue(): \Ramsey\Uuid\Uuid
    {
        return $this->value;
    }

    public function equals(Uuid $uuid):bool {
        return $this->value->equals($uuid->internalValue());
    }
}