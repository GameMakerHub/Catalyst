<?php
namespace Catalyst\Tests\Model;

use Catalyst\Model\YoYo\Resource;
use Catalyst\Service\JsonService;
use Catalyst\Service\StorageService;
use Catalyst\Tests\MockStorageTestCase;
use Mockery\MockInterface;

class ResourceTest extends MockStorageTestCase
{
    private $jsonString = <<<EOL
{
    "Key": "0bb36c74-dc11-4a76-8ff5-0d89046b21bf",
    "Value": {
        "id": "d4700bac-6283-45d1-899f-16cccf665418",
        "resourcePath": "views\\\\0bb36c74-dc11-4a76-8ff5-0d89046b21bf.yy",
        "resourceType": "GMFolder"
    }
}
EOL;

    /** @var Resource */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockStorage
            ->shouldReceive('getJson')
            ->once()
            ->with('views\\0bb36c74-dc11-4a76-8ff5-0d89046b21bf.yy')
            ->andReturn(new \stdClass());

        $this->subject = Resource::createFromObject(JsonService::decode($this->jsonString));
    }

    public function testGetJsonReturnsSameAdValuesCorrect()
    {
        $this->assertEquals(
            $this->jsonString,
            JsonService::encode($this->subject)
        );

        $this->assertEquals(
            JsonService::decode($this->jsonString),
            $this->subject->jsonSerialize()
        );
    }

    public function testValues()
    {
        $this->assertSame('0bb36c74-dc11-4a76-8ff5-0d89046b21bf', (string) $this->subject->key());
    }
}