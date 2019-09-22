<?php
namespace Catalyst\Tests\Model;

use Catalyst\Model\Uuid;

class UuidTest extends \PHPUnit\Framework\TestCase
{
    private $randomUuid = '0bb36c74-dc11-4a76-8ff5-0d89046b21bf';

    public function testCreate()
    {
        $this->assertEquals(
            Uuid::createFromString($this->randomUuid),
            Uuid::createFromString($this->randomUuid)
        );
    }

    public function testEquals()
    {
        $this->assertTrue(
            Uuid::createFromString($this->randomUuid)->equals(
                Uuid::createFromString($this->randomUuid)
            )
        );
    }

    public function testCreateRandom()
    {
        $this->assertNotSame(
            Uuid::createRandom(),
            Uuid::createRandom()
        );

        $this->assertNotSame(
            (string) Uuid::createRandom(),
            (string) Uuid::createRandom()
        );
    }

    public function testJsonSerialize()
    {
        $this->assertSame($this->randomUuid, Uuid::createFromString($this->randomUuid)->jsonSerialize());
    }

    public function testInternalValue()
    {
        $this->assertEquals(\Ramsey\Uuid\Uuid::fromString($this->randomUuid), Uuid::createFromString($this->randomUuid)->internalValue());
    }
}